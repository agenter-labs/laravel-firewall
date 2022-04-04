<?php

namespace AgenterLab\Firewall;

use AgenterLab\Firewall\Commands\UnblockIp;
use AgenterLab\Firewall\Events\AttackDetected;
use AgenterLab\Firewall\Listeners\BlockIp;
use AgenterLab\Firewall\Listeners\CheckLogin;
use AgenterLab\Firewall\Listeners\NotifyUsers;
use Illuminate\Auth\Events\Failed as LoginFailed;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->publishes([
            __DIR__ . '/Config/firewall.php'                                            => config_path('firewall.php'),
            __DIR__ . '/Migrations/2019_07_15_000000_create_firewall_ips_table.php'     => database_path('migrations/2019_07_15_000000_create_firewall_ips_table.php'),
            __DIR__ . '/Migrations/2019_07_15_000000_create_firewall_logs_table.php'    => database_path('migrations/2019_07_15_000000_create_firewall_logs_table.php'),
            __DIR__ . '/Resources/lang'                                                 => resource_path('lang/vendor/firewall'),
        ], 'firewall');

        $this->registerMiddleware($router);
        $this->registerListeners();
        $this->registerTranslations();
        $this->registerCommands();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/firewall.php', 'firewall');

        $this->app->register(\Jenssegers\Agent\AgentServiceProvider::class);
    }

    /**
     * Register middleware.
     *
     * @param Router $router
     *
     * @return void
     */
    public function registerMiddleware($router)
    {
        $router->middlewareGroup('firewall.all', config('firewall.all_middleware'));
        $router->aliasMiddleware('firewall.agent', 'AgenterLab\Firewall\Middleware\Agent');
        $router->aliasMiddleware('firewall.bot', 'AgenterLab\Firewall\Middleware\Bot');
        $router->aliasMiddleware('firewall.ip', 'AgenterLab\Firewall\Middleware\Ip');
        $router->aliasMiddleware('firewall.geo', 'AgenterLab\Firewall\Middleware\Geo');
        $router->aliasMiddleware('firewall.lfi', 'AgenterLab\Firewall\Middleware\Lfi');
        $router->aliasMiddleware('firewall.php', 'AgenterLab\Firewall\Middleware\Php');
        $router->aliasMiddleware('firewall.referrer', 'AgenterLab\Firewall\Middleware\Referrer');
        $router->aliasMiddleware('firewall.rfi', 'AgenterLab\Firewall\Middleware\Rfi');
        $router->aliasMiddleware('firewall.session', 'AgenterLab\Firewall\Middleware\Session');
        $router->aliasMiddleware('firewall.sqli', 'AgenterLab\Firewall\Middleware\Sqli');
        $router->aliasMiddleware('firewall.swear', 'AgenterLab\Firewall\Middleware\Swear');
        $router->aliasMiddleware('firewall.url', 'AgenterLab\Firewall\Middleware\Url');
        $router->aliasMiddleware('firewall.whitelist', 'AgenterLab\Firewall\Middleware\Whitelist');
        $router->aliasMiddleware('firewall.xss', 'AgenterLab\Firewall\Middleware\Xss');
    }

    /**
     * Register listeners.
     *
     * @return void
     */
    public function registerListeners()
    {
        $this->app['events']->listen(AttackDetected::class, BlockIp::class);
        $this->app['events']->listen(AttackDetected::class, NotifyUsers::class);
        $this->app['events']->listen(LoginFailed::class, CheckLogin::class);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $lang_path = resource_path('lang/vendor/firewall');

        if (is_dir($lang_path)) {
            $this->loadTranslationsFrom($lang_path, 'firewall');
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'firewall');
        }
    }

    public function registerCommands()
    {
        $this->commands(UnblockIp::class);

        $this->app->booted(function () {
            app(Schedule::class)->command('firewall:unblockip')->everyMinute();
        });
    }
}
