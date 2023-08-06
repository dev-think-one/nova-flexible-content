<?php

namespace NovaFlexibleContent\Tests\Http;

use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\Fixtures\Models\User;
use NovaFlexibleContent\Tests\TestCase;

class DeleteFileFieldTest extends TestCase
{
    protected User $admin;

    protected string $uriKey;

    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        Post::factory()->count(7)->create();
        $this->post = Post::factory()->create([
            'title'   => 'Foo bar baz',
            'content' => '[{"layout":"feature-list","key":"cbMGRJZ4Rqx57NnG","collapsed":true,"attributes":{"title":"Foo Title","links":[{"layout":"link","key":"cmzQ5WZLhFZCOSZw","collapsed":true,"attributes":{"text":"Baz text","link":"Baz link","file":"k8UrCGosJsh7ULp2uVhihfNfcPYpj0BPmqDzKWwp.jpg"}}]}}]',
        ]);
        $this->uriKey = \NovaFlexibleContent\Tests\Fixtures\Nova\Resources\Post::uriKey();
    }

    /** @test */
    public function delete_file_in_flexible()
    {
        $content = json_decode($this->post->refresh()->content, true);
        $this->assertNotEmpty($content[0]['attributes']['links'][0]['attributes']['file']);
        $this->assertStringEndsWith('.jpg', $content[0]['attributes']['links'][0]['attributes']['file']);

        $response = $this->actingAs($this->admin)
            ->delete("/nova-api/{$this->uriKey}/{$this->post->getKey()}/field/cmzQ5WZLhFZCOSZw__file");

        $response->assertSuccessful();

        $content = json_decode($this->post->refresh()->content, true);
        $this->assertNull($content[0]['attributes']['links'][0]['attributes']['file']);
    }

}
