<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticMktrWp
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MktrWp\\Tracker\\' => 15
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MktrWp\\Tracker\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Tracker',
        )
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticMktrWp::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticMktrWp::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticMktrWp::$classMap;

        }, null, ClassLoader::class);
    }
}
