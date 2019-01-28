<?php

if (! function_exists('env')) {

    /**
     * Get the value of an environment variable
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return $value;
    }
}

if (! function_exists('config')) {

    /**
     * Get the configuration value from config file
     * For example:
     * config('database.host') loads config value from database.php using 'host' as the key
     *
     * @param  string $key
     * @return mixed
     */
    function config($key)
    {
        static $config = null;
        if ($config === null) {
            $config = \Northwoods\Config\ConfigFactory::make(
                [
                    'directory' => APP_ROOT . '/config',
                ]
            );
        }

        return $config->get($key);
    }
}

if (! function_exists('view')) {

    /**
     * Loads view file
     *
     * @param  string $viewFile Path of view file relative to view folder
     * @param  array $viewModel data to be passed to view file
     * @return mixed
     */
    function view($viewFile, array $viewModel = [])
    {
        $templates = new \League\Plates\Engine(APP_ROOT . '/view/', null);

        return $templates->render($viewFile, $viewModel);
    }
}

if (! function_exists('url')) {

    /**
     * Generates full url for routes
     */
    function url($path = '')
    {
        $path = ltrim($path, '/');
        $basePath = config('app.url');
        if ($path) {
            return $basePath . '/' . $path;
        }
        return $basePath;
    }
}

if (! function_exists('asset')) {

    /**
     * Generates full url for assets like CSS, JS, image files
     */
    function asset($path = '')
    {
        $path = ltrim($path, '/');
        $basePath = config('app.url');
        if ($path) {
            return $basePath . '/' . $path;
        }
        return $basePath;
    }
}
