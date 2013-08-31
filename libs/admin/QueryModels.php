<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.3.0
 * @copyright chajr/bluetree
 */
class Libs_Admin_QueryModels
{
    /**
     * get admin data by given encrypted password
     *
     * @param  string $encryptedPassword
     * @return Libs_Mysql
     */
    static function getAdmin ($encryptedPassword)
    {
        $query = "SELECT * FROM admin WHERE admin_password = '$encryptedPassword'";

        return new Libs_Mysql($query);
    }

    /**
     * set admin date from logged in method
     * 
     * @param integer $logNumber
     * @param string $logTime
     * @param integer $uId
     * @return Libs_Mysql
     */
    static function setLogInAdmin ($logNumber, $logTime, $uId)
    {
        $query = "UPDATE
            admin SET
            admin_lognum = '$logNumber',
            admin_logdate = '$logTime'
            WHERE
            admin_id = '$uId'";

        return new Libs_Mysql($query);
    }

    /**
     * get all terms from database
     *
     * @return Libs_Mysql
     */
    static function getTerms ()
    {
        $query = "SELECT * FROM terminy ORDER BY data_przyjazdu DESC";

        return new Libs_Mysql($query);
    }

    /**
     * get all reservations or reservation with given id
     * 
     * @param integer $reservationId
     * @return Libs_Mysql
     */
    static function getReservations($reservationId)
    {
        $where = '';
        if ($reservationId) {
            $where = " WHERE id = '$reservationId'";
        }
        $query = "SELECT * FROM rezerwacje" . $where;

        return new Libs_Mysql($query);
    }
}