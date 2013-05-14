<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application;

if (!file_exists(__DIR__.'/config/config.php')) {
    die('You must provide a config file!');
}

$app['config'] = require_once __DIR__.'/config/config.php';
$app['debug'] = $app['config']['debug'];
$app['protocol'] = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === false ? 'http' : 'https';

if (!isset($app['config']['repositories']) || empty($app['config']['repositories'])) {
    die('You must provide at least on repo in the config file!');
}

require_once __DIR__.'/../src/Resources/translations/translations.php';

$app['github'] = new GithubApi_v3\Api();
$app['bookmarklet'] = new Bookmarklet\Bookmarklet($app['config']['base_url']);

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => array(
        __DIR__.'/../src/Resources/views',
    ),
));
// 
// $app->register(new Silex\Provider\SymfonyBridgesServiceProvider(), array(
//    'symfony_bridges.class_path' => __DIR__ . '/../vendor/symfony/src'
// ));

$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

require __DIR__.'/../src/app.php';

return $app;
