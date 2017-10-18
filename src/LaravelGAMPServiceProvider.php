<?php

namespace Irazasyed\LaravelGAMP;

use Illuminate\Support\ServiceProvider;
use TheIconic\Tracking\GoogleAnalytics\Analytics;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Foundation\Application as LaravelApplication;

class LaravelGAMPServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->setupConfig($this->app);
    }

    /**
     * Setup the config.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     */
    protected function setupConfig(Application $app)
    {
        $source = __DIR__.'/config/gamp.php';

        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => config_path('gamp.php')]);
        } elseif ($app instanceof LumenApplication) {
            $app->configure('gamp');
        }

        $this->mergeConfigFrom($source, 'gamp');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerAnalytics($this->app);
    }

    /**
     * Initialize Analytics Library with Default Config.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     */
    protected function registerAnalytics(Application $app)
    {
        $app->singleton('gamp', function ($app) {
            $config = $app['config'];

            $analytics = new Analytics($config->get('gamp.is_ssl', false), $config->get('gamp.is_disabled', false));

            $analytics->setProtocolVersion($config->get('gamp.protocol_version', 1))
                ->setTrackingId($config->get('gamp.tracking_id'));

            if ($config->get('gamp.anonymize_ip', false)) {
                $analytics->setAnonymizeIp('1');
            }

            if ($config->get('gamp.async_requests', false)) {
                $analytics->setAsyncRequest(true);
            }

            return $analytics;
        });

        $app->alias('gamp', Analytics::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['gamp', Analytics::class];
    }
}
