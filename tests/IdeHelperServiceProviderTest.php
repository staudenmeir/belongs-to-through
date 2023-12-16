<?php

namespace Tests;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider as BarryvdhIdeHelperServiceProvider;
use Orchestra\Testbench\TestCase;
use Staudenmeir\BelongsToThrough\IdeHelper\BelongsToThroughRelationsHook;
use Staudenmeir\BelongsToThrough\IdeHelperServiceProvider;

class IdeHelperServiceProviderTest extends TestCase
{
    public function testAutoRegistrationOfModelHook(): void
    {
        $this->app->loadDeferredProvider(BarryvdhIdeHelperServiceProvider::class);
        $this->app->loadDeferredProvider(IdeHelperServiceProvider::class);

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->app->get('config');

        $this->assertContains(
            BelongsToThroughRelationsHook::class,
            $config->get('ide-helper.model_hooks'),
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            BarryvdhIdeHelperServiceProvider::class,
            IdeHelperServiceProvider::class,
        ];
    }
}
