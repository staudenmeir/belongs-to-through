<?php

use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Traits\BelongsToThrough;

/**
 * Test BelongsToThrough.
 */
class BelongsToThroughTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => __DIR__.'/database.sqlite',
            'prefix'   => '',
        ]);
    }

    public function test_through_one()
    {
        $district = District::where('id', 1)->first();

        $this->assertNotNull($district->country);
        $this->assertEquals(1, $district->country->id);
    }

    public function test_through_two()
    {
        $city = City::where('id', 1)->first();

        $this->assertNotNull($city->country);
        $this->assertEquals(1, $city->country->id);
    }

    public function test_eager_loading()
    {
        $cities = City::with('country')->where('id', '>', 0)->get();

        $this->assertCount(16, $cities);

        foreach ($cities as $city) {
            $this->assertEquals(ceil($city->id / 8), $city->country->id);
        }
    }
}

class Country extends Model
{
}

class State extends Model
{
}

class District extends Model
{
    use BelongsToThrough;

    public function country()
    {
        return $this->belongsToThrough(Country::class, State::class);
    }
}

class City extends Model
{
    use BelongsToThrough;

    public function country()
    {
        return $this->belongsToThrough(Country::class, [State::class, District::class]);
    }
}
