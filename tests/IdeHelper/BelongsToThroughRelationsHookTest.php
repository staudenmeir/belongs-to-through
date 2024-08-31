<?php

namespace Tests\IdeHelper;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Illuminate\Database\Capsule\Manager as DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase;
use Staudenmeir\BelongsToThrough\IdeHelper\BelongsToThroughRelationsHook;
use Tests\IdeHelper\Models\Comment;

class BelongsToThroughRelationsHookTest extends TestCase
{
    use MockeryPHPUnitIntegration;

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
    }

    public function testRun(): void
    {
        $command = Mockery::mock(ModelsCommand::class);
        $command->shouldReceive('setProperty')->once()->with(
            'country',
            '\Tests\IdeHelper\Models\Country',
            true,
            false,
            '',
            true
        );

        $hook = new BelongsToThroughRelationsHook();
        $hook->run($command, new Comment());
    }
}
