
# Needle

Small dependency injection container for PHP 5.3+

[![Build Status](https://secure.travis-ci.org/maximebf/needle.png)](http://travis-ci.org/maximebf/needle)

## Installation

The easiest way to install Needle is using [Composer](https://github.com/composer/composer)
with the following requirement:

    {
        "require": {
            "maximebf/needle": ">=0.1.0"
        }
    }

Alternatively, you can [download the archive](https://github.com/maximebf/needle/zipball/master) 
and add the src/ folder to PHP's include path:

    set_include_path('/path/to/src' . PATH_SEPARATOR . get_include_path());

Needle does not provide an autoloader but follows the [PSR-0 convention](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).  
You can use the following snippet to autoload Needle classes:

    spl_autoload_register(function($className) {
        if (substr($className, 0, 6) === 'Needle') {
            $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
            require_once $filename;
        }
    });
