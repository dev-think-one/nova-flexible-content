<?php

namespace NovaFlexibleContent\Tests\Http;

use Illuminate\Support\Str;
use NovaFlexibleContent\Http\FlexibleAttribute;
use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\Fixtures\Models\User;
use NovaFlexibleContent\Tests\TestCase;

class UpdateFlexibleTest extends TestCase
{
    protected User $admin;

    protected string $uriKey;

    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin  = User::factory()->create();
        Post::factory()->count(7)->create();
        $this->post   = Post::factory()->create();
        $this->uriKey = \NovaFlexibleContent\Tests\Fixtures\Nova\Resources\Post::uriKey();
    }

    /** @test */
    public function if_no_flexible_registered_then_go_to_next()
    {
        $groupKey    = 'c'.Str::random(15);
        $groupPrefix = FlexibleAttribute::formatGroupPrefix($groupKey);

        $response = $this->actingAs($this->admin)
            ->put("/nova-api/{$this->uriKey}/{$this->post->getKey()}", [
                'title'                                         => 'FooBar',
            ]);

        $response->assertSuccessful();
        $response->assertJsonPath('id', $this->post->getKey());
        $response->assertJsonPath('resource.content', null);
    }

    /** @test */
    public function update_flexible_field_first_level()
    {
        $groupKey    = 'c'.Str::random(15);
        $groupPrefix = FlexibleAttribute::formatGroupPrefix($groupKey);

        $response = $this->actingAs($this->admin)
            ->put("/nova-api/{$this->uriKey}/{$this->post->getKey()}", [
                'title'                                         => 'FooBar',
                FlexibleAttribute::REGISTER_FLEXIBLE_FIELD_NAME => json_encode(['content']),
                'content'                                       => json_encode([
                    [
                        'layout'     => 'simple_number',
                        'key'        => $groupKey,
                        'collapsed'  => true,
                        'attributes' => [
                            "{$groupPrefix}order" => 33,
                        ],
                    ],
                ]),
            ]);

        $response->assertSuccessful();
        $response->assertJsonPath('id', $this->post->getKey());
        $response->assertJsonPath('resource.content.0.attributes.order', 33);
    }

    /** @test */
    public function update_flexible_field_validation_error()
    {
        $groupKey    = 'c'.Str::random(15);
        $groupPrefix = FlexibleAttribute::formatGroupPrefix($groupKey);

        $response = $this->actingAs($this->admin)
            ->put("/nova-api/{$this->uriKey}/{$this->post->getKey()}", [
                'title'                                         => 'FooBar',
                FlexibleAttribute::REGISTER_FLEXIBLE_FIELD_NAME => json_encode(['content']),
                'content'                                       => json_encode([
                    [
                        'layout'     => 'simple_number',
                        'key'        => $groupKey,
                        'collapsed'  => true,
                        'attributes' => [
                            "{$groupPrefix}order" => 'baz',
                        ],
                    ],
                ]),
            ]);

        $response->assertRedirect();

        $response->assertSessionHasErrors(['content.0.attributes.order']);
    }



    /** @test */
    public function update_flexible_field_json_validation_error()
    {
        $groupKey    = 'c'.Str::random(15);
        $groupPrefix = FlexibleAttribute::formatGroupPrefix($groupKey);

        $response = $this->actingAs($this->admin)
            ->putJson("/nova-api/{$this->uriKey}/{$this->post->getKey()}", [
                'title'                                         => 'FooBar',
                FlexibleAttribute::REGISTER_FLEXIBLE_FIELD_NAME => json_encode(['content']),
                'content'                                       => json_encode([
                    [
                        'layout'     => 'simple_number',
                        'key'        => $groupKey,
                        'collapsed'  => true,
                        'attributes' => [
                            "{$groupPrefix}order" => 'baz',
                        ],
                    ],
                ]),
            ]);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors(["{$groupPrefix}order"]);
    }
}
