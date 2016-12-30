<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita35a3f66259aae1f927466b65a66aafb
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Workerman\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita35a3f66259aae1f927466b65a66aafb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita35a3f66259aae1f927466b65a66aafb::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
