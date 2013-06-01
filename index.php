<?php
/**
 * main file that loads all required libraries, start core class and display required content
 * 
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.4.0
 * @copyright chajr/bluetree
 */
define('BASE_PATH', dirname(__FILE__));
require_once 'libs/Core.php';
require_once 'libs/Connection.php';
require_once 'libs/Mysql.php';
require_once 'libs/Render.php';
require_once 'libs/Form.php';
require_once 'libs/Valid.php';
require_once 'libs/Xml.php';
require_once 'libs/Liber.php';
require_once 'libs/QueryModels.php';
try{
    $core = new Libs_Core();
    echo $core->display();
}catch (Exception $exception){
    $exception->getMessage();
}
