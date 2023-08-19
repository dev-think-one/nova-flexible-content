<?php

namespace NovaFlexibleContent\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use NovaFlexibleContent\Tests\Fixtures\Factories\UserFactory;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasFactory;

    protected $table = 'users';

    protected $guarded = [];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
