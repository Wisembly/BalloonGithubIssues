<?php

class Autoloader
{
    static public function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(new self, 'autoload'));
    }

    static public function autoload($class)
    {
        if (0 !== strpos($class, 'Balloon_')) {
            return;
        }

        if (file_exists($file =__DIR__.'/'.str_replace('Balloon_', '', $class).'.class.php')) {
            require $file;
        }
    }
}