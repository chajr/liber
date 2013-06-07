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

    static function getRooms ($id = NULL)
    {
        $query = '';
        if ($id) {
            $query = "WHERE id = '$id'";
        }
        
        $query = "SELECT * FROM pokoje $query";
        return new Libs_Mysql($query);
    }
}