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
}
