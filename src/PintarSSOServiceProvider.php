<?php

namespace Banjarmasinkota\PintarSSO;

use Illuminate\Support\ServiceProvider;

/**
 * Class PasswordServiceProvider
 */
class PintarSSOServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('PintarSSO', PintarSSO::class);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/pintar_sso.php' => config_path('pintar_sso.php')
        ]);
    }
}
