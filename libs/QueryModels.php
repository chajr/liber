<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.2.0
 * @copyright chajr/bluetree
 */
class Libs_QueryModels
{
    static function getTerms ($currentTime)
    {
        $query = "SELECT * FROM terminy WHERE data_wyjazdu >= '$currentTime'";
        return new Libs_Mysql($query);
    }

    static function getRooms ()
    {
        $query = "SELECT * FROM pokoje";
        return new Libs_Mysql($query);
    }
}