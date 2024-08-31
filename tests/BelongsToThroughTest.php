<?php

namespace Tests;

use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\CustomerAddress;
use Tests\Models\Post;
use Tests\Models\User;
use Tests\Models\VendorCustomer;

class BelongsToThroughTest extends TestCase
{
    public function testLazyLoading(): void
    {
        $country = Comment::firstOrFail()->country;

        $this->assertEquals(1, $country?->id);
    }

    public function testLazyLoadingWithSingleThroughModel(): void
    {
        $user = Comment::firstOrFail()->user;

        $this->assertEquals(11, $user?->id);
    }

    public function testLazyLoadingWithPrefix(): void
    {
        $country = Comment::findOrFail(34)->countryWithPrefix;

        $this->assertEquals(1, $country?->id);
    }

    public function testLazyLoadingWithCustomForeignKeys(): void
    {
        $country = Comment::findOrFail(35)->countryWithCustomForeignKeys;

        $this->assertEquals(1, $country?->id);
    }

    public function testLazyLoadingWithSoftDeletes(): void
    {
        $country = Comment::findOrFail(33)->country;

        $this->assertFalse($country?->exists);
    }

    public function testLazyLoadingWithDefault(): void
    {
        $country = Comment::findOrFail(33)->country;

        $this->assertInstanceOf(Country::class, $country);
        $this->assertFalse($country->exists);
    }

    public function testLazyLoadingWithAlias(): void
    {
        $comment = Comment::findOrFail(35)->grandparent;

        $this->assertEquals(33, $comment?->id);
    }

    public function testEagerLoading(): void
    {
        $comments = Comment::with('country')->get();

        $this->assertEquals(1, $comments[0]?->country?->id);
        $this->assertEquals(2, $comments[1]?->country?->id);
        $this->assertInstanceOf(Country::class, $comments[2]?->country);
        $this->assertFalse($comments[2]->country->exists);
    }

    public function testEagerLoadingWithPrefix(): void
    {
        $comments = Comment::with('countryWithPrefix')->get();

        $this->assertNull($comments[0]?->countryWithPrefix);
        $this->assertEquals(1, $comments[3]?->countryWithPrefix?->id);
    }

    public function testLazyEagerLoading(): void
    {
        $comments = Comment::all()->load('country');

        $this->assertEquals(1, $comments[0]?->country?->id);
        $this->assertEquals(2, $comments[1]?->country?->id);
        $this->assertInstanceOf(Country::class, $comments[2]?->country);
        $this->assertFalse($comments[2]->country->exists);
    }

    public function testExistenceQuery(): void
    {
        $comments = Comment::has('country')->get();

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testExistenceQueryWithPrefix(): void
    {
        $comments = Comment::has('countryWithPrefix')->get();

        $this->assertEquals([34], $comments->pluck('id')->all());
    }

    public function testWithTrashed(): void
    {
        /** @var User $user */
        $user = Comment::findOrFail(33)->user()
            ->withTrashed()
            ->first();

        $this->assertEquals(13, $user->id);
    }

    public function testWithTrashedIntermediate(): void
    {
        /** @var Country $country */
        $country = Comment::findOrFail(33)->country()
            ->withTrashed(['users.deleted_at'])
            ->first();

        $this->assertEquals(3, $country->id);
    }

    public function testWithTrashedIntermediateAndWhereHas(): void
    {
        $comments = Comment::has('countryWithTrashedUser')->get();

        $this->assertEquals([31, 32, 33], $comments->pluck('id')->all());
    }

    public function testGetThroughParents(): void
    {
        $throughParents = Comment::firstOrFail()->country()->getThroughParents();

        $this->assertCount(2, $throughParents);
        $this->assertInstanceOf(User::class, $throughParents[0]);
        $this->assertInstanceOf(Post::class, $throughParents[1]);
    }

    public function testGetThroughWithCustomizedLocalKeys(): void
    {
        $addresses = CustomerAddress::with('vendorCustomer')->get();

        $this->assertEquals(41, $addresses[0]?->vendorCustomer?->id);
        $this->assertEquals(42, $addresses[1]?->vendorCustomer?->id);
        $this->assertInstanceOf(VendorCustomer::class, $addresses[1]?->vendorCustomer);
        $this->assertFalse($addresses[2]?->vendorCustomer()->exists());
    }
}
