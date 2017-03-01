<?php

/**
 * Includes a file if it exists.
 *
 * @param string $file
 *   File to include.
 *
 * @return bool|mixed
 *   FALSE if the file not included.
 */
function includeIfExists($file)
{
  return file_exists($file) ? include $file : false;
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
  echo 'You must set up the project dependencies using `composer install`'.PHP_EOL.
    'See https://getcomposer.org/download/ for instructions on installing Composer'.PHP_EOL;
  exit(1);
}

return $loader;
