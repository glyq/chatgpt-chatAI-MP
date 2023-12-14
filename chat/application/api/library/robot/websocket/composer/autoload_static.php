<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5aea6a01c0dca5f3c99422434f0c7b49
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WebSocket\\' => 10,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'Psr\\Http\\Message\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WebSocket\\' => 
        array (
            0 => __DIR__ . '/..' . '/textalk/websocket/lib',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
            1 => __DIR__ . '/..' . '/psr/http-factory/src',
        ),
    );

    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/..' . '/phrity/util-errorhandler/src',
        1 => __DIR__ . '/..' . '/phrity/net-uri/src',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5aea6a01c0dca5f3c99422434f0c7b49::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5aea6a01c0dca5f3c99422434f0c7b49::$prefixDirsPsr4;
            $loader->fallbackDirsPsr4 = ComposerStaticInit5aea6a01c0dca5f3c99422434f0c7b49::$fallbackDirsPsr4;
            $loader->classMap = ComposerStaticInit5aea6a01c0dca5f3c99422434f0c7b49::$classMap;

        }, null, ClassLoader::class);
    }
}
