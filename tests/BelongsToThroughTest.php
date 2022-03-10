<?php

namespace Tests;

use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\User;

class BelongsToThroughTest extends TestCase
{
    public function testLazyLoading()
    {
        $country = Comment::first()->country;

        $this->assertEquals(1, $country->id);
    }

    public function testLazyLoadingWithSingleThroughModel()
    {
        $user = Comment::first()->user;

        $this->assertEquals(11, $user->id);
    }

    public function testLazyLoadingWithPrefix()
    {
        $country = Comment::find(34)->countryWithPrefix;

        $this->assertEquals(1, $country->id);
    }

    public function testLazyLoadingWithCustomForeignKeys()
    {
        $country = Comment::find(35)->countryWithCustomForeignKeys;

        $this->assertEquals(1, $country->id);
    }

    public function testLazyLoadingWithSoftDeletes()
    {
        $country = Comment::find(33)->country;

        $this->assertFalse($country->exists);
    }

    public function testLazyLoadingWithDefault()
    {
        $country = Comment::find(33)->country;

        $this->assertInstanceOf(Country::class, $country);
        $this->assertFalse($country->exists);
    }

    public function testLazyLoadingWithAlias()
    {
        $comment = Comment::find(35)->grandparent;

        $this->assertEquals(33, $comment->id);
    }

    public function testEagerLoading()
    {
        $comments = Comment::with('country')->get();

        $this->assertEquals(1, $comments[0]->country->id);
        $this->assertEquals(2, $comments[1]->country->id);
        $this->assertInstanceOf(Country::class, $comments[2]->country);
        $this->assertFalse($comments[2]->country->exists);
    }

    public function testEagerLoadingWithPrefix()
    {
        $comments = Comment::with('countryWithPrefix')->get();

        $this->assertNull($comments[0]->countryWithPrefix);
        $this->assertEquals(1, $comments[3]->countryWithPrefix->id);
    }

    public function testLazyEagerLoading()
    {
        $comments = Comment::all()->load('country');

        $this->assertEquals(1, $comments[0]->country->id);
        $this->assertEquals(2, $comments[1]->country->id);
        $this->assertInstanceOf(Country::class, $comments[2]->country);
        $this->assertFalse($comments[2]->country->exists);
    }

    public function testExistenceQuery()
    {
        $comments = Comment::has('country')->get();

        $this->assertEquals([31, 32], $comments->pluck('id')->all());
    }

    public function testExistenceQueryWithPrefix()
    {
        $comments = Comment::has('countryWithPrefix')->get();

        $this->assertEquals([34], $comments->pluck('id')->all());
    }

    public function testWithTrashed()
    {
        $user = Comment::find(33)->user()
            ->withTrashed()
            ->first();

        $this->assertEquals(13, $user->id);
    }

    public function testWithTrashedIntermediate()
    {
        $country = Comment::find(33)->country()
            ->withTrashed(['users.deleted_at'])
            ->first();

        $this->assertEquals(3, $country->id);
    }

    public function testWithTrashedIntermediateAndWhereHas()
    {
        $comments = Comment::has('countryWithTrashedUser')->get();

        $this->assertEquals([31, 32, 33], $comments->pluck('id')->all());
    }

    public function testGetThroughParents()
    {
        $throughParents = Comment::first()->country()->getThroughParents();

        $this->assertCount(2, $throughParents);
        $this->assertInstanceOf(User::class, $throughParents[0]);
        $this->assertInstanceOf(Post::class, $throughParents[1]);
    }
}
