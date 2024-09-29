<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase as Base;
use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\CustomerAddress;
use Tests\Models\Post;
use Tests\Models\User;
use Tests\Models\VendorCustomer;
use Tests\Models\VendorCustomerAddress;

abstract class TestCase extends Base
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = new DB();
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $db->setAsGlobal();
        $db->bootEloquent();

        $this->migrate();

        $this->seed();
    }

    protected function migrate(): void
    {
        DB::schema()->create('countries', function (Blueprint $table) {
            $table->increments('id');
        });

        DB::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('country_id');
            $table->softDeletes();
        });

        DB::schema()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('custom_user_id')->nullable();
        });

        DB::schema()->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id')->nullable();
            $table->unsignedInteger('custom_post_id')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
        });

        DB::schema()->create('vendor_customers', function (Blueprint $table) {
            $table->increments('id');
        });

        DB::schema()->create('customer_addresses', function (Blueprint $table) {
            $table->increments('id');
        });

        DB::schema()->create('vendor_customer_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vendor_customer_id');
            $table->unsignedInteger('customer_address_id');
        });
    }

    protected function seed(): void
    {
        Model::unguard();

        Country::create(['id' => 1]);
        Country::create(['id' => 2]);
        Country::create(['id' => 3]);

        User::create(['id' => 11, 'country_id' => 1, 'deleted_at' => null]);
        User::create(['id' => 12, 'country_id' => 2, 'deleted_at' => null]);
        User::create(['id' => 13, 'country_id' => 3, 'deleted_at' => Carbon::now()->subDay()]);

        Post::create(['id' => 21, 'user_id' => 11, 'custom_user_id' => null]);
        Post::create(['id' => 22, 'user_id' => 12, 'custom_user_id' => null]);
        Post::create(['id' => 23, 'user_id' => 13, 'custom_user_id' => null]);
        Post::create(['id' => 24, 'user_id' => null, 'custom_user_id' => 11]);

        Comment::create(['id' => 31, 'post_id' => 21, 'custom_post_id' => null, 'parent_id' => null]);
        Comment::create(['id' => 32, 'post_id' => 22, 'custom_post_id' => null, 'parent_id' => null]);
        Comment::create(['id' => 33, 'post_id' => 23, 'custom_post_id' => null, 'parent_id' => null]);
        Comment::create(['id' => 34, 'post_id' => null, 'custom_post_id' => 21, 'parent_id' => 33]);
        Comment::create(['id' => 35, 'post_id' => null, 'custom_post_id' => 24, 'parent_id' => 34]);

        VendorCustomer::create(['id' => 41]);
        VendorCustomer::create(['id' => 42]);
        VendorCustomer::create(['id' => 43]);

        CustomerAddress::create(['id' => 51]);
        CustomerAddress::create(['id' => 52]);
        CustomerAddress::create(['id' => 53]);

        VendorCustomerAddress::create(['id' => 61, 'vendor_customer_id' => 41, 'customer_address_id' => 51]);
        VendorCustomerAddress::create(['id' => 62, 'vendor_customer_id' => 42, 'customer_address_id' => 52]);

        Model::reguard();
    }
}
