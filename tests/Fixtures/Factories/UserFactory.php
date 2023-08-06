<?php

namespace NovaFlexibleContent\Tests\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaFlexibleContent\Tests\Fixtures\Models\User;

/**
 * @extends \Orchestra\Testbench\Factories\UserFactory<User>
 */
class UserFactory extends \Orchestra\Testbench\Factories\UserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;
}
