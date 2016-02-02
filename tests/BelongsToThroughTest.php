<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;
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
        $district = Stub_Test_Model_District::where('id', 1)->first();

        $this->assertNotNull($district->country);
        $this->assertEquals(1, $district->country->id);
    }

    public function test_through_two()
    {
        $city = Stub_Test_Model_City::where('id', 1)->first();

        $this->assertNotNull($city->country);
        $this->assertEquals(1, $city->country->id);
    }

    public function test_eager_loading()
    {
        $cities = Stub_Test_Model_City::with('country')->where('id', '<', 17)->get();

        $this->assertCount(16, $cities);

        foreach ($cities as $city) {
            $this->assertEquals(ceil($city->id / 8), $city->country->id);
        }
    }

    public function test_has_relation()
    {
        $cities_with_country = Stub_Test_Model_City::has('country')->get();
        $all_cities = Stub_Test_Model_City::all();

        $this->assertCount(16, $cities_with_country);
        $this->assertCount(18, $all_cities);
    }

    public function test_prefixed_foreign_key()
    {
        $city = Stub_Test_Model_City::where('id', 1)->first();

        $this->assertNotNull($city->otherCountry);
        $this->assertEquals(1, $city->otherCountry->id);
    }

    public function test_custom_foreign_key()
    {
        $district = Stub_Test_Model_District::where('id', 1)->first();

        $this->assertNotNull($district->countryOffshore);
        $this->assertEquals(1, $district->countryOffshore->id);
    }

    public function test_custom_foreign_key_through_two()
    {
        $city = Stub_Test_Model_City::where('id', 1)->first();

        $this->assertNotNull($city->offshoreCountry);
        $this->assertEquals(1, $city->offshoreCountry->id);
    }
}

class Stub_Parent_Model extends Eloquent
{
    public function getForeignKey()
    {
        return Str::singular($this->getTable()).'_id';
    }
}

class Stub_Test_Model_Contient extends Stub_Parent_Model
{
    protected $table = 'continents';

    public function countries()
    {
        return $this->hasMany(Stub_Test_Model_Country::class);
    }
}

class Stub_Test_Model_Country extends Stub_Parent_Model
{
    protected $table = 'countries';

    public function continent()
    {
        return $this->belongsTo(Stub_Test_Model_Contient::class);
    }
}

class Stub_Test_Model_State extends Stub_Parent_Model
{
    protected $table = 'states';
}

class Stub_Test_Model_Offshore_State extends Stub_Parent_Model
{
    protected $table = 'offshore_states';
}

class Stub_Test_Model_District extends Stub_Parent_Model
{
    use BelongsToThrough;

    protected $table = 'districts';

    public function country()
    {
        return $this->belongsToThrough(Stub_Test_Model_Country::class, Stub_Test_Model_State::class);
    }

    public function countryOffshore()
    {
        return $this->belongsToThrough(Stub_Test_Model_Country::class, [[Stub_Test_Model_Offshore_State::class, 'state_id']]);
    }
}

class Stub_Test_Model_City extends Stub_Parent_Model
{
    use BelongsToThrough;

    protected $table = 'cities';

    public function country()
    {
        return $this->belongsToThrough(Stub_Test_Model_Country::class,
            [Stub_Test_Model_State::class, Stub_Test_Model_District::class]);
    }

    public function otherCountry()
    {
        return $this->belongsToThrough(Stub_Test_Model_Country::class,
            [Stub_Test_Model_State::class, Stub_Test_Model_District::class], null, 'other_');
    }

    public function offshoreCountry()
    {
        return $this->belongsToThrough(Stub_Test_Model_Country::class,
            [[Stub_Test_Model_Offshore_State::class, 'state_id'], Stub_Test_Model_District::class]);
    }
}
