<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Zarezerwuj pokój</title>
        <link href="css/meyer_reset_css.css" rel="stylesheet" type="text/css" />
        <link href="css/960_24_col.css" rel="stylesheet" type="text/css" />
        <link href="css/elusive-webfont.css" rel="stylesheet" type="text/css" />
        <link href="css/elusive-webfont-ie7.css" rel="stylesheet" type="text/css" />
        <link href="css/jquery-ui-1.10.3.custom.css" rel="stylesheet" type="text/css" />
        <link href="css/main.css" rel="stylesheet" type="text/css" />
        <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body>
        <?php
        define('BASE_PATH', dirname(__FILE__));
        require_once 'libs/Core.php';
        require_once 'libs/Connection.php';
        require_once 'libs/Mysql.php';
        require_once 'libs/Render.php';
        require_once 'libs/Form.php';
        require_once 'libs/Valid.php';
        require_once 'libs/Xml.php';
        require_once 'libs/Liber.php';
        try{
            $core = new Libs_Core();
        }catch (Exception $exception){
            $exception->getMessage();
        }
        ?>
    </body>
    <script src="js/jquery-1.9.1.js"></script>
    <script src="js/jquery-ui-1.10.3.custom.min.js"></script>
    <script src="js/scripts.js"></script>
</html>