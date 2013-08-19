<?php
/**
 * main file that loads all required libraries, start core class and display required content
 * 
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 1.0.0
 * @copyright chajr/bluetree
 */
define('BASE_PATH', dirname(__FILE__));
require_once 'libs/Core.php';
require_once 'libs/Connection.php';
require_once 'libs/Mysql.php';
require_once 'libs/Render.php';
require_once 'libs/Valid.php';
require_once 'libs/Xml.php';
require_once 'libs/QueryModels.php';
require_once 'libs/phpmailer/class.phpmailer.php';
try{
    $core = new Libs_Core();
    echo $core->display();
}catch (Exception $exception){
    echo '<div class="error">
            <i class="icon-error-alt"></i>
            ' . $exception->getMessage() . '
        </div>';
}
