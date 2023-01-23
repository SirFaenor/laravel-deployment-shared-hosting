<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/


/**
 * Moves environment path to /env.
 * /env/.master.env is always loaded
 */
$app->useEnvironmentPath(__DIR__.'/../env');
$app->loadEnvironmentFrom('.master.env');


/**
 * Looks for specific environment files
 */
$app->afterLoadingEnvironment(function() use($app) {
    
    // look for a file named based on execution host (without "www")
    if (!$app->runningInConsole() && isset($_SERVER)) {
        $envFile = '.'.str_replace("www.", "", $_SERVER["HTTP_HOST"]).'.env';
    }

    // look for a file reserved for command line execution
    if ($app->runningInConsole()) {
        $envFile = '.cli.env';
    }

    // load/overwrite variables from selected file
    if (file_exists($app->environmentPath().'/'.$envFile)) {
        $dotenv = Dotenv\Dotenv::create($app->environmentPath(), $envFile);
        $dotenv->overload();
    }

});

return $app;
