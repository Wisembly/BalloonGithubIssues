<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\FormServiceProvider,
    Silex\Provider\TranslationServiceProvider,
    Silex\Provider\SessionServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider;

use Symfony\Component\Translation\Loader\YamlFileLoader;

use GithubApi_v3\Api,
    Bookmarklet\Bookmarklet;

$app = new Application;

if (!file_exists(__DIR__.'/config/config.php')) {
    die('You must provide a config file!');
}

$app['config'] = require_once __DIR__.'/config/config.php';
$app['debug'] = $app['config']['debug'];
$app['protocol'] = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === false ? 'http' : 'https';

if (!isset($app['config']['repositories']) || empty($app['config']['repositories'])) {
    die('You must provide at least on repo in the config file!');
}

// require_once __DIR__.'/../src/Resources/translations/translations.php';

$app['github'] = new Api;
$app['bookmarklet'] = new Bookmarklet($app['config']['base_url']);

$app
    ->register(new TwigServiceProvider, array(
        'twig.path'       => array(
            __DIR__.'/../src/Resources/views',
        ),
    ))
    ->register(new TranslationServiceProvider, array(
        'locale_fallback'       => 'en',
    ))
    ->register(new FormServiceProvider)
    ->register(new SessionServiceProvider)
    ->register(new UrlGeneratorServiceProvider);

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());

    $translator->addResource('yaml', __DIR__.'/../src/Resources/translations/messages.en.yml', 'en');
    $translator->addResource('yaml', __DIR__.'/../src/Resources/translations/messages.fr.yml', 'fr');

    return $translator;
}));

require __DIR__.'/../src/app.php';

return $app;
