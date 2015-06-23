<?php namespace FintechFab\Smstraffic\Providers;

use FintechFab\Smstraffic\SmsTraffic;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class SmstrafficServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        /**
         * \Illuminate\Config\Repository
         */
        $config = $this->app['config'];
        $config = $config->get('services.smstraffic');

        $this->app->bind(SmsTraffic::class, function () use ($config) {
            $guzzle = new Client();

            return new SmsTraffic($guzzle, $config['from'], $config['login'], $config['password'], $config['latin'],
                $config['pretend']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
