<?php

namespace NovaFlexibleContent\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NovaFlexibleContent\Concerns\HasFlexible;
use NovaFlexibleContent\Tests\Fixtures\Factories\PostFactory;

class Post extends Model
{
    use HasFlexible, HasFactory;

    protected $table = 'posts';

    protected $guarded = [];

    protected static function newFactory(): PostFactory
    {
        return new PostFactory();
    }
}
