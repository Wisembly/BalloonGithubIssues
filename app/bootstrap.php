<?php

require __DIR__.'/autoload.php';

$app = new Silex\Application;

$app['config'] = require_once __DIR__.'/config/config.php';
$app['debug'] = $app['config']['debug'];

if (!isset($app['config']['repositories']) || empty($app['config']['repositories'])) {
    die('You must provide at least on repo in the config file!');
}

require_once __DIR__.'/../src/Resources/translations/translations.php';

$app['github'] = new GithubApi_v3\Api();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => array(
        __DIR__.'/../src/Resources/views',
        __DIR__.'/../vendor/Symfony/Bridge/Twig/Resources/views/Form',
    ),
    'twig.class_path' => __DIR__.'/../vendor/Twig/lib',
));

$app->register(new Silex\Provider\SymfonyBridgesServiceProvider(), array(
   'symfony_bridges.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new Silex\Provider\FormServiceProvider(), array(
    'form.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback'           => 'en',
    'translation.class_path'    => __DIR__.'/../vendor/symfony/src',
));

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

require __DIR__.'/../src/app.php';

return $app;