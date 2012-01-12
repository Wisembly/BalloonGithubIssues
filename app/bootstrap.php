<?php

require_once __DIR__.'/../silex.phar';

$app = new Silex\Application();
$app['config'] = require_once __DIR__.'/config.php';
$app['debug'] = $app['config']['debug'];

require_once __DIR__.'/../lib/Autoloader.php';
Autoloader::register();

$app['github'] = new Balloon_GithubApi();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __DIR__.'/../views',
    'twig.class_path' => __DIR__.'/../vendor/twig/lib',
));

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());