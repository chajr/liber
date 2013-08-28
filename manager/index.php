<?php
/**
 * main file that loads all required libraries, start core class and display required content
 *
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.3.1
 * @copyright chajr/bluetree
 */
$path = str_replace('/manager', '', dirname(__FILE__));
define('BASE_PATH', $path);
if (!isset($_SESSION)) {
    session_start();
}
require_once BASE_PATH . '/libs/Core.php';
require_once BASE_PATH . '/libs/admin/Core.php';
require_once BASE_PATH . '/libs/admin/Loger.php';
require_once BASE_PATH . '/libs/QueryModels.php';
require_once BASE_PATH . '/libs/admin/QueryModels.php';
require_once BASE_PATH . '/libs/Connection.php';
require_once BASE_PATH . '/libs/Mysql.php';
require_once BASE_PATH . '/libs/Render.php';
require_once BASE_PATH . '/libs/Valid.php';
require_once BASE_PATH . '/libs/Xml.php';
require_once BASE_PATH . '/libs/QueryModels.php';
require_once BASE_PATH . '/libs/phpmailer/class.phpmailer.php';
try{
    $core = new Libs_Admin_Core();
    echo $core->display();
}catch (Exception $exception){
    echo '<div class="error">
            <i class="icon-error-alt"></i>
            ' . $exception->getMessage() . '
        </div>';
}