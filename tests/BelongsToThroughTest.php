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
        $relation = $this->getRelation();
        $model = m::mock('Illuminate\Database\Eloquent\Model');
        $relation->getRelated()->shouldReceive('newCollection')->andReturnUsing(function ($array = []) {
            return new Collection($array);
        });
        $model->shouldReceive('setRelation')->once()->with('foo', m::type('Illuminate\Database\Eloquent\Collection'));
        $models = $relation->initRelation([$model], 'foo');
        $this->assertEquals([$model], $models);
    }

    public function testEagerConstraintsAreProperlyAdded()
    {
        $relation = $this->getRelation();
        $relation->getQuery()->shouldReceive('whereIn')->once()->with('users.id', [1, 2]);
        $model1 = new EloquentBelongsToThroughModelStub;
        $model1->id = 1;
        $model2 = new EloquentBelongsToThroughModelStub;
        $model2->id = 2;
        $relation->addEagerConstraints([$model1, $model2]);
    }

    public function testModelsAreProperlyMatchedToParents()
    {
        $relation = $this->getRelation();
        $result1 = new EloquentBelongsToThroughModelStub;
        $result1->country_id = 1;
        $result2 = new EloquentBelongsToThroughModelStub;
        $result2->country_id = 2;
        $result3 = new EloquentBelongsToThroughModelStub;
        $result3->country_id = 2;
        $model1 = new EloquentBelongsToThroughModelStub;
        $model1->id = 1;
        $model2 = new EloquentBelongsToThroughModelStub;
        $model2->id = 2;
        $model3 = new EloquentBelongsToThroughModelStub;
        $model3->id = 3;
        $relation->getRelated()->shouldReceive('newCollection')->andReturnUsing(function ($array) {
            return new Collection($array);
        });
        $models = $relation->match([$model1, $model2, $model3], new Collection([$result1, $result2, $result3]), 'foo');
        $this->assertEquals(1, $models[0]->foo->country_id);
        $this->assertEquals(1, count($models[0]->foo));
        $this->assertEquals(2, $models[1]->foo->country_id);
        $this->assertEquals(1, count($models[1]->foo));
        $this->assertEquals(0, count($models[2]->foo));
    }

    public function testIgnoreSoftDeletingParent()
    {
        list($builder, $country, , $firstKey, $secondKey) = $this->getRelationArguments();
        $user = new EloquentHasManyThroughSoftDeletingModelStub;

        $builder->shouldReceive('whereNull')->with('users.deleted_at')->once()->andReturn($builder);

        $relation = new BelongsToThrough($builder, $country, $user, $firstKey, $secondKey);
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
        $builder->shouldReceive('where')->with('users.id', '=', 1);

        $user = m::mock('Illuminate\Database\Eloquent\Model');
        $user->shouldReceive('getTable')->andReturn('users');
        $user->shouldReceive('getForeignKey')->andReturn('user_id');
        $user->shouldReceive('getKeyName')->andReturn('id');


        $post = m::mock('Illuminate\Database\Eloquent\Model');
        $post->shouldReceive('offsetGet')->with('user_id')->andReturn(1);

        $country = m::mock('Illuminate\Database\Eloquent\Model');
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

class EloquentHasManyThroughSoftDeletingModelStub extends Illuminate\Database\Eloquent\Model
{
    use SoftDeletes;
    public $table = 'users';
}
