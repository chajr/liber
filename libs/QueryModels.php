<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.1.0
 * @copyright chajr/bluetree
 */
class Libs_QueryModels
{
    static function getTerms ($currentTime)
    {
        $query = "SELECT * FROM terminy WHERE data_wyjazdu >= '$currentTime'";
        return new Libs_Mysql($query);
    }
}