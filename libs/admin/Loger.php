<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package admin
 * @version 1.0.0
 * @copyright chajr/bluetree
 */
class Libs_Admin_Loger
{
    public static function logOn($uid, $options, $group)
    {
        $code                               = self::code();
        $_SESSION['log_class']['log']       = TRUE;
        $_SESSION['log_class']['uid']       = $uid;
        $_SESSION['log_class']['code']      = $code;
        $_SESSION['log_class']['options']   = $options;
        $_SESSION['log_class']['group']     = $group;
        $_SESSION['log_class']['time']      = time() + 60*60;
    }

    public static function logOut()
    {
        $_SESSION['log_class'] = array();
        unset($_SESSION['log_class']);
    }

    public static function verification()
    {
        if (!isset($_SESSION['log_class']['log']) ||
            !isset($_SESSION['log_class']['uid']) ||
            !isset($_SESSION['log_class']['code']) ||
            !isset($_SESSION['log_class']['options']) ||
            !isset($_SESSION['log_class']['group']) ||
            !isset($_SESSION['log_class']['time']) ||
            !$_SESSION['log_class']['log']) {

            return FALSE;
        } else {
            if ($_SESSION['log_class']['code'] === self::code()) {
                if ($_SESSION['log_class']['options']{0} === '0') {
                    throw new LibraryException('no_reg');
                }

                if ($_SESSION['log_class']['options']{1} === '0') {
                    throw new LibraryException('blocked');
                }

                if ($_SESSION['log_class']['time'] < time()) {
                    return FALSE;
                }

                $_SESSION['log_class']['time'] = time() + 60*60;
                @session_regenerate_id();
                $_SESSION['log_class']['code'] = self::code();

                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    private static function code()
    {
        $client     = $_SERVER['HTTP_USER_AGENT'];
        $ip         = $_SERVER['REMOTE_ADDR'];
        $code       = hash(
            'sha256',
            $client . $ip
        );

        return $code;
    }
}