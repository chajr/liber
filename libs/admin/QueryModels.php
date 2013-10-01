<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.9.0
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
     * @param integer|null $reservationId
     * @return Libs_Mysql
     */
    static function getTerms ($reservationId = NULL)
    {
        $where = '';
        if ($reservationId) {
            $where = " WHERE id_reservation='$reservationId'";
        }
        $query = "SELECT * FROM terminy $where ORDER BY data_przyjazdu DESC";

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
     * @param integer|boolean $reservationId
     * @return Libs_Mysql
     */
    static function getRoomsWithTerms($reservationId = NULL)
    {
        $where = '';
        if ($reservationId) {
            $where = " AND id = '$reservationId'";
        }

        $query = "SELECT
            pokoje.*, terminy.id_reservation, terminy.id_pokoje, rezerwacje.od,
            rezerwacje.do, rezerwacje.opcje, terminy.id as term_id
            FROM
            rezerwacje , terminy, pokoje
            WHERE
            terminy.id_reservation = rezerwacje.id
            $where
            AND terminy.id_pokoje = pokoje.id";

        return new Libs_Mysql($query);
    }

    /**
     * set payment information (payment done or not)
     * 
     * @param integer $reservationId
     * @param string $value
     * @return Libs_Mysql
     */
    static function setPayment($reservationId, $value)
    {
        $query = "UPDATE rezerwacje SET uwagi='$value' WHERE id='$reservationId'";
        return new Libs_Mysql($query);
    }

    /**
     * return all promotions
     *
     * @return Libs_Mysql
     */
    static function getPromotions()
    {
        $query = "SELECT * FROM promotions";
        return new Libs_Mysql($query);
    }

    /**
     * update promotion
     * 
     * @param integer $promotionId
     * @param integer $days
     * @param integer $percent
     * @return Libs_Mysql
     */
    static function updatePromotion($promotionId, $days, $percent)
    {
        $query = "UPDATE promotions
            SET
            days='$days',
            percent='$percent'
            WHERE
            promotion_id='$promotionId'";
        return new Libs_Mysql($query);
    }

    static function removePromotion($promotionId)
    {
        
    }

    /**
     * create new promotion
     * 
     * @param integer $days
     * @param integer $percent
     * @return Libs_Mysql
     */
    static function createPromotion($days, $percent)
    {
        $query = "INSERT INTO
            promotions
            (days, percent)
            VALUES
            ('$days', '$percent')
        ";

        return new Libs_Mysql($query);
    }
}