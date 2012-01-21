<?php

require __DIR__.'/../silex.phar';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'           => __DIR__.'/../vendor',
    'GithubApi_v3'      => __DIR__.'/../lib',
    'Bookmarklet'       => __DIR__.'/../lib',
));

$loader->registerPrefixes(array(
    'Twig_Extensions_'  => __DIR__.'/../vendor/Twig-extensions/lib',
    'Twig_'             => __DIR__.'/../vendor/Twig/lib',
));
$loader->register();