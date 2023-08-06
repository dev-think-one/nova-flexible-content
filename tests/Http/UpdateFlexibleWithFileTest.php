<?php

namespace NovaFlexibleContent\Tests\Http;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use NovaFlexibleContent\Http\FlexibleAttribute;
use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\Fixtures\Models\User;
use NovaFlexibleContent\Tests\TestCase;

class UpdateFlexibleWithFileTest extends TestCase
{
    protected User $admin;

    protected string $uriKey;

    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        Post::factory()->count(7)->create();
        $this->post   = Post::factory()->create();
        $this->uriKey = \NovaFlexibleContent\Tests\Fixtures\Nova\Resources\Post::uriKey();
    }

    /** @test */
    public function update_flexible_field_first_level()
    {
        $groupKey    = 'c' . Str::random(15);
        $groupPrefix = FlexibleAttribute::formatGroupPrefix($groupKey);

        $response = $this->actingAs($this->admin)
            ->put("/nova-api/{$this->uriKey}/{$this->post->getKey()}", [
                'title'                                         => 'FooBar',
                FlexibleAttribute::REGISTER_FLEXIBLE_FIELD_NAME => json_encode(['content']),
                'content'                                       => json_encode([
                    [
                        'layout'     => 'feature-list',
                        'key'        => $groupKey,
                        'collapsed'  => true,
                        'attributes' => [
                            "{$groupPrefix}title" => 'Foo Title',
                            "{$groupPrefix}src"   => FlexibleAttribute::FILE_INDICATOR . "{$groupPrefix}src",
                        ],
                    ],
                ]),
                FlexibleAttribute::FILE_INDICATOR . "{$groupPrefix}src" => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        $response->assertSuccessful();
        $response->assertJsonPath('id', $this->post->getKey());
        $response->assertJsonPath('resource.content.0.attributes.title', 'Foo Title');
        $this->assertStringEndsWith('.jpg', $response->json('resource.content.0.attributes.src'));

    }

    /** @test */
    public function update_flexible_field_sub_levels()
    {
        $groupKeyLevel1    = 'c' . Str::random(15);
        $groupPrefixLevel1 = FlexibleAttribute::formatGroupPrefix($groupKeyLevel1);

        $groupKeyLevel2    = 'c' . Str::random(15);
        $groupPrefixLevel2 = FlexibleAttribute::formatGroupPrefix($groupKeyLevel2);

        $response = $this->actingAs($this->admin)
            ->put("/nova-api/{$this->uriKey}/{$this->post->getKey()}", [
                'title'                                         => 'FooBar',
                FlexibleAttribute::REGISTER_FLEXIBLE_FIELD_NAME => json_encode(['content']),
                'content'                                       => json_encode([
                    [
                        'layout'     => 'feature-list',
                        'key'        => $groupKeyLevel1,
                        'collapsed'  => true,
                        'attributes' => [
                            "{$groupPrefixLevel1}title"                     => 'Foo Title',
                            "{$groupPrefixLevel1}src"                       => FlexibleAttribute::FILE_INDICATOR . "{$groupPrefixLevel1}src",
                            FlexibleAttribute::REGISTER_FLEXIBLE_FIELD_NAME => ["{$groupPrefixLevel1}links"],
                            "{$groupPrefixLevel1}links"                     => json_encode([
                                [
                                    'layout'     => 'link',
                                    'key'        => $groupKeyLevel2,
                                    'collapsed'  => true,
                                    'attributes' => [
                                        "{$groupPrefixLevel2}text"        => 'Baz text',
                                        "{$groupPrefixLevel2}link"        => 'Baz link',
                                        "{$groupPrefixLevel2}file"        => FlexibleAttribute::FILE_INDICATOR . "{$groupPrefixLevel2}file",
                                        "{$groupPrefixLevel2}second_file" => FlexibleAttribute::FILE_INDICATOR . "{$groupPrefixLevel2}second_file",
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ]),
                FlexibleAttribute::FILE_INDICATOR . "{$groupPrefixLevel2}file" => UploadedFile::fake()->image('file.jpg'),
                // second_file will be empty
                FlexibleAttribute::FILE_INDICATOR . "{$groupPrefixLevel2}second_file" => null,
                // fake files will be processes bit not saved as not exists
                FlexibleAttribute::FILE_INDICATOR . "{$groupPrefixLevel2}fake_files" => [
                    UploadedFile::fake()->image('foo.jpg'),
                    UploadedFile::fake()->image('bar.jpg'),
                ],
            ]);

        $response->assertSuccessful();
        $response->assertJsonPath('id', $this->post->getKey());
        $response->assertJsonPath('resource.content.0.attributes.title', 'Foo Title');
        $response->assertJsonPath('resource.content.0.attributes.links.0.attributes.text', 'Baz text');
        $response->assertJsonPath('resource.content.0.attributes.links.0.attributes.link', 'Baz link');
        $this->assertStringEndsWith('.jpg', $response->json('resource.content.0.attributes.links.0.attributes.file'));
        $this->assertNull($response->json('resource.content.0.attributes.links.0.attributes.second_file'));
    }
}
