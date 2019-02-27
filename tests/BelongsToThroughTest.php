<?php

namespace Tests;

use Illuminate\Support\Arr;
use Tests\Models\Comment;
use Tests\Models\Post;

class BelongsToThroughTest extends TestCase
{
    public function testLazyLoading()
    {
        $country = Comment::first()->country;

        $this->assertEquals(1, $country->id);
    }

    public function testLazyLoadingWithSingleThroughModel()
    {
        $country = Post::first()->country;

        $this->assertEquals(1, $country->id);
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

        $this->assertNull($country);
    }

    public function testEagerLoading()
    {
        $comments = Comment::with('country')->get();

        $this->assertEquals(1, $comments[0]->country->id);
        $this->assertEquals(2, $comments[1]->country->id);
        $this->assertNull($comments[2]->country);
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
        $this->assertNull($comments[2]->country);
    }

    public function testExistenceQuery()
    {
        $comments = Comment::has('country')->get();

        $this->assertEquals([31, 32], Arr::pluck($comments, 'id'));
    }

    public function testExistenceQueryWithPrefix()
    {
        $comments = Comment::has('countryWithPrefix')->get();

        $this->assertEquals([34], Arr::pluck($comments, 'id'));
    }
}
