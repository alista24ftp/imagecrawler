<?php
function loadClass($class)
{
    $path = __DIR__ . "/classes/";
    require_once $path . $class . '.php';
}

spl_autoload_register('loadClass');