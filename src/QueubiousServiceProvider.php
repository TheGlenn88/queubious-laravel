<?php

namespace Queubious;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use Queubious\Http\Middleware\QueubiousMiddleware;

class QueubiousServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/queubious.php' => config_path(
                        'queubious.php'
                    ),
                ],
                'queubious-config'
            );
        }

        $this->configureMiddleware();
    }

    /**
     * Configure the Queubious middleware and priority.
     *
     * @return void
     */
    protected function configureMiddleware()
    {
        $kernel = $this->app->make(Kernel::class);

        $kernel->prependToMiddlewarePriority(QueubiousMiddleware::class);
    }
}
