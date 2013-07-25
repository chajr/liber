<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.3.0
 * @copyright chajr/bluetree
 */
class Libs_QueryModels
{
    static function getTerms ($currentTime)
    {
        $query = "SELECT * FROM terminy WHERE data_wyjazdu >= '$currentTime'";

        return new Libs_Mysql($query);
    }

    static function getRooms ($id = NULL)
    {
        $query = '';
        if ($id) {
            $query = "WHERE id = '$id'";
        }

        $query = "SELECT * FROM pokoje $query";
        return new Libs_Mysql($query);
    }

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

    static function saveTerm($roomId, $from, $to)
    {
        $query = "INSERT INTO 
            terminy
            (id_pokoje, data_przyjazdu, data_wyjazdu)
            VALUES
            ('$roomId', '$from', '$to')
        ";

        return new Libs_Mysql($query);
    }
}