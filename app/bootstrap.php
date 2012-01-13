<?php

require __DIR__.'/../silex.phar';

$app = new Silex\Application;

$app['config'] = require_once __DIR__.'/../Ressources/config/config.php';
$app['debug'] = $app['config']['debug'];

if (!isset($app['config']['repositories']) || empty($app['config']['repositories'])) {
    die('You must provide at least on repo in the config file!');
}

require_once __DIR__.'/../Ressources/translations/translations.php';
require_once __DIR__.'/../lib/Autoloader.php';
Autoloader::register();

$app['github'] = new Balloon_GithubApi();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => array(
        __DIR__.'/../Ressources/views',
        __DIR__.'/../vendor/symfony/src/Symfony/Bridge/Twig/Resources/views/Form',
    ),
    'twig.class_path' => __DIR__.'/../vendor/twig/lib',
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