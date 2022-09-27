<?php

namespace Edu\Signatrue;

use Edu\Signatrue\Middleware\SignatrueAuthenticate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class SignatrueProvider extends ServiceProvider
{
    protected $middlewares = [
        'auth.signatrue' => SignatrueAuthenticate::class
    ];

    public function register()
    {

    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->aliasMiddlewares();
        $this->registerGuards();
    }

    protected function aliasMiddlewares()
    {
        $router = $this->app['router'];
        $method = method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware';
        foreach ($this->middlewares as $alias => $middleware) {
            $router->$method($alias, $middleware);
        }
    }

    protected function registerGuards()
    {
        Auth::extend('signature', function ($app, $name, $config) {
            return new SignatrueGuard(
                new Signatrue(),
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );
        });
    }
}
