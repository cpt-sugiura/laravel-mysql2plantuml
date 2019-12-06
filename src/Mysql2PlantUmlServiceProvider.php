<?php

namespace Mysql2PlantUml;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Mysql2PlantUml\App\Console\Commands\MySQL2PlantUML;

class Mysql2PlantUmlServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__.'/config/mysql2plantuml.php' => config_path('mysql2plantuml.php'),
            ]
        );
        $this->registerCommands();
        $this->registerViewPaths();
        $this->registerDataBaseConnect();
    }

    /**
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->app->singleton(
            'command.mysql2plantuml',
            function () {
                return new MySQL2PlantUML();
            }
        );
        $this->commands(['command.mysql2plantuml']);
    }

    /**
     * Register the template hint paths.
     *
     * @return void
     */
    protected function registerViewPaths(): void
    {
        $paths = collect(config('view.paths'));

        View::replaceNamespace(
            'puml',
            $paths->map(
                function ($path) {
                    return "{$path}/puml";
                }
            )->push(__DIR__.'/resources/views')->all()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function provides(): array
    {
        return [
            'command.mysql2plantuml',
        ];
    }

    private function registerDataBaseConnect(): void
    {
        $DS = DIRECTORY_SEPARATOR;
        $conf = Config::get('mysql2plantuml') ?? require __DIR__.$DS.'config'.$DS.'mysql2plantuml.php';
        Config::set('mysql2plantuml', $conf);

        $this->app['config']['database.connections'] = Arr::add(
            $this->app['config']['database.connections'],
            'mysql_information_schema',
            config('mysql2plantuml.connection')
        );
    }

}
