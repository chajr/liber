<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.4.2
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
     * @param integer|boolean $reservationId
     * @return Libs_Mysql
     */
    static function getReservations($reservationId = NULL)
    {
        $where = '';
        if ($reservationId) {
            $where = " WHERE id = '$reservationId'";
        }
        $query = "SELECT * FROM rezerwacje" . $where . " ORDER BY od DESC";

        return new Libs_Mysql($query);
    }

    /**
     * get full data to display room details for specific term on list of term
     * 
     * @return Libs_Mysql
     */
    static function getRoomsWithTerms()
    {
        $query = "SELECT
            pokoje.*, terminy.id_reservation, .terminy.id_pokoje,
            rezerwacje.opcje, terminy.id as term_id
            FROM
            rezerwacje , terminy, pokoje
            WHERE
            terminy.id_reservation = rezerwacje.id
            AND terminy.id_pokoje = pokoje.id";

        return new Libs_Mysql($query);
    }
}