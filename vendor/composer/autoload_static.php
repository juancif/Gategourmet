<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2185d2f99bcd56787481d9357a5972d3
{
    public static $prefixLengthsPsr4 = array (
<<<<<<< HEAD
<<<<<<< HEAD
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
=======
        'Z' => 
        array (
            'ZipStream\\' => 10,
        ),
=======
>>>>>>> parent of cf18b17 (Actualizacion36)
        'T' => 
        array (
            'Tests\\PhpOffice\\Math\\' => 21,
        ),
        'P' => 
        array (
            'PhpOffice\\PhpWord\\' => 18,
            'PhpOffice\\Math\\' => 15,
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Tests\\PhpOffice\\Math\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoffice/math/tests/Math',
        ),
        'PhpOffice\\PhpWord\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoffice/phpword/src/PhpWord',
        ),
        'PhpOffice\\Math\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoffice/math/src/Math',
        ),
>>>>>>> cf18b17ca5bb4e27e51670217984bdff237faf3b
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
<<<<<<< HEAD
<<<<<<< HEAD
=======
        'Matrix\\' => 
        array (
            0 => __DIR__ . '/..' . '/markbaker/matrix/classes/src',
        ),
        'Complex\\' => 
        array (
            0 => __DIR__ . '/..' . '/markbaker/complex/classes/src',
        ),
>>>>>>> cf18b17ca5bb4e27e51670217984bdff237faf3b
=======
>>>>>>> parent of cf18b17 (Actualizacion36)
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2185d2f99bcd56787481d9357a5972d3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2185d2f99bcd56787481d9357a5972d3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2185d2f99bcd56787481d9357a5972d3::$classMap;

        }, null, ClassLoader::class);
    }
}
