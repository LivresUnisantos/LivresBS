<?php
spl_autoload_register('ClassAutoLoader');

function ClassAutoLoader($className) {
    $path = dirname(__DIR__,1).DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR;
    $extension = ".class.php";
    $fullpath = $path . $className . $extension;

    if (!file_exists($fullpath)) {
        return false;
    }    
    include_once $path . $className . $extension; 
}
?>