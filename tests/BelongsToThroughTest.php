<?php

/**
 * This file belongs to belongsToThrough.
 *
 * Author: Rahul Kadyan, <hi@znck.me>
 * Find license in root directory of this project.
 */

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mockery as m;
use Znck\Eloquent\Relations\BelongsToThrough;

/**
 * Test BelongsToThrough
 */
class BelongsToThroughTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        m::close();
    }

    // Borrowing test cases from laravel framework
    public function testRelationIsProperlyInitialized()
    {
        $this->assertTrue(true);
        // TODO add tests.
    }

    protected function getRelation()
    {
        list($builder, $country, $user, $firstKey, $localKey) = $this->getRelationArguments();

        return new BelongsToThrough($builder, $country, $user, $firstKey, $localKey);
    }

    protected function getRelationArguments()
    {
        $builder = m::mock('Illuminate\Database\Eloquent\Builder');
        $builder->shouldReceive('join')->once()->with('users', 'countries.id', '=', 'users.country_id');
        $builder->shouldReceive('addSelect')->with(['users.id as __related_through_key'])->with(['countries.*']);
        $builder->shouldReceive('where')->with('users.id', '=', 1);

        $user = m::mock('ZnckModel');
        $user->shouldReceive('getTable')->andReturn('users');
        $user->shouldReceive('getForeignKey')->andReturn('user_id');
        $user->shouldReceive('getKeyName')->andReturn('id');


        $post = m::mock('ZnckModel');
        $post->shouldReceive('offsetGet')->with('user_id')->andReturn(1);

        $country = m::mock('ZnckModel');
        $country->shouldReceive('getQualifiedKeyName')->andReturn('countries.id');
        $country->shouldReceive('getTable')->andReturn('countries');

        $builder->shouldReceive('getModel')->andReturn($country);

        return [$builder, $post, $user, 'country_id', 'user_id'];
    }
}

class EloquentBelongsToThroughModelStub extends Illuminate\Database\Eloquent\Model
{
    public $country_id = 'foreign.value';
}

class ZnckModel extends Illuminate\Database\Eloquent\Model {
    use Znck\Eloquent\Relations\BelongsToThroughTrait;
}

class EloquentHasManyThroughSoftDeletingModelStub extends Illuminate\Database\Eloquent\Model
{
    use SoftDeletes;
    public $table = 'users';
}
