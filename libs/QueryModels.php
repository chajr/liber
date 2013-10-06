<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 1.2.0
 * @copyright chajr/bluetree
 */
class Libs_QueryModels
{
    /**
     * get all terms from database that data_wyjazdu is bigger than current date
     * 
     * @param  string $currentTime
     * @return Libs_Mysql
     */
    static function getTerms ($currentTime)
    {
        $query = "SELECT * FROM terminy WHERE data_wyjazdu >= '$currentTime'";

        return new Libs_Mysql($query);
    }

    /**
     * get all, or given room values
     * 
     * @param integer|boolean $id
     * @return Libs_Mysql
     */
    static function getRooms ($id = NULL)
    {
        $query = '';
        if ($id) {
            $query = "WHERE id = '$id'";
        }

        $query = "SELECT * FROM pokoje $query";
        return new Libs_Mysql($query);
    }

    /**
     * save reservation info to database
     * 
     * @param string $imie
     * @param string $nazwisko
     * @param string $od
     * @param string $do
     * @param string $mail
     * @param string $telefon
     * @param string $ulica
     * @param string $numer
     * @param string $miasto
     * @param string $kod
     * @param string $opcje
     * @return Libs_Mysql
     */
    static function saveReservation(
        $imie, $nazwisko, $od, $do, $mail, $telefon, $ulica, $numer, $miasto,
        $kod, $opcje
    ){
        $query = "INSERT INTO
            rezerwacje
            (imie, nazwisko, od, do, mail, telefon, ulica, numer, miasto, kod, opcje)
            VALUES
            ('$imie', '$nazwisko', '$od', '$do', '$mail', '$telefon', '$ulica',
             '$numer', '$miasto', '$kod', '$opcje')
        ";

        return new Libs_Mysql($query);
    }

    /**
     * save room reservation range
     * 
     * @param integer $roomId
     * @param integer $reservationId
     * @param string $from
     * @param string $to
     * @return Libs_Mysql
     */
    static function saveTerm($roomId, $reservationId, $from, $to)
    {
        $query = "INSERT INTO 
            terminy
            (id_pokoje, id_reservation, data_przyjazdu, data_wyjazdu)
            VALUES
            ('$roomId', $reservationId, '$from', '$to')
        ";

        return new Libs_Mysql($query);
    }

    /**
     * remove reservation on given id
     * 
     * @param integer $reservationId
     * @return Libs_Mysql
     */
    static function removeReservation($reservationId)
    {
        $query = "DELETE FROM
            rezerwacje
            WHERE
            id = '$reservationId'
        ";

        return new Libs_Mysql($query);
    }

    /**
     * remove term
     * 
     * @param integer $termId
     * @return Libs_Mysql
     */
    static function removeTerm($termId)
    {
        $query = "DELETE FROM
            terminy
            WHERE
            id = '$termId'
        ";

        return new Libs_Mysql($query);
    }

    /**
     * remove terms on given reservation id
     * 
     * @param integer $reservationId
     * @return Libs_Mysql
     */
    static function removeTerms($reservationId)
    {
        $query = "DELETE FROM
            terminy
            WHERE
            id_reservation = '$reservationId'
        ";

        return new Libs_Mysql($query);
    }

    /**
     * get promotion for given days count
     * 
     * @param integer $days
     * @return Libs_Mysql
     */
    static function getPromotion($days)
    {
        $query = "SELECT * FROM promotions WHERE days='$days'";

        return new Libs_Mysql($query);
    }
}