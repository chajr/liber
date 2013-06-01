<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.5.0
 * @copyright chajr/bluetree
 */
class Libs_Core
{
    /**
     * contains readed options as array
     * @var array
     */
    protected $_options = array();

    /**
     * contains all content to display
     * @var string
     */
    protected $_display = '';

    /**
     * list of unavailable rooms
     * @var array
     */
    protected $_lockedRooms = array();

    /**
     * start liber core class
     */
    public function __construct()
    {
        $this->_readOptions();
        $this->_setConnection();
        $this->_controller();
    }

    /**
     * read all options from main configuration file
     */
    protected function _readOptions()
    {
        $xml = new Libs_Xml();
        $xml->loadFile(BASE_PATH . '/cfg/main.xml', TRUE);
        $block = $xml->documentElement;
        if (!$block) {
            throw Exception($xml->err);
        }
        foreach ($block->childNodes as $nod) {
            if ($nod->nodeType != 1) {
                continue;
            }
            if ($nod->firstChild) {
                $val = array();
                foreach ($nod->childNodes as $value) {
                    if ($value->nodeType === 3) {
                        $val['description'] =  $value->nodeValue;
                    } else {
                        $val[$value->getAttribute('name')] = $value->getAttribute('value');
                    }
                }
            } else {
                $val = $nod->getAttribute('value');
            }
            $id = $nod->getAttribute('id');
            $this->_options[$id] = $val;
        }
    }

    /**
     * set connection to database
     */
    protected function _setConnection()
    {
        $connection = new Libs_Connection($this->_options);
        if ($connection->err) {
            throw Exception($connection->err);
        }
    }

    /**
     * start rendering of required page
     */
    protected function _controller()
    {
        switch ($_POST['page']) {
            case 'rooms':
                $this->_checkRange($_POST['from'], $_POST['to']);
                $this->_roomsRender($_POST['from'], $_POST['to']);
                break;
            default:
                $this->_baseRender();
                break;
        }
    }

    /**
     * render main page with all html structure (scripts, styles etc.)
     */
    protected function _baseRender()
    {
        $header         = new Libs_Render('header');
        $breadcrumbs    = new Libs_Render('breadcrumbs');
        $footer         = new Libs_Render('footer');
        $calendar       = new Libs_Render('calendar');
        $steps          = new Libs_Render('index');

        $stream = '';
        $stream .= $header->render();
        $stream .= $breadcrumbs->render();
        $stream .= $calendar->render();
        $stream .= $steps->render();
        $stream .= $footer->render();

        $this->_display = $stream;
    }

    /**
     * return rendered content
     */
    public function display()
    {
        return $this->_display;
    }

    /**
     * create list of rooms with their status
     * @param date $from
     * @param date $to
     */
    protected function _roomsRender($from, $to)
    {
        $this->_getLockedRooms($from, $to);

        $roomsTemplate  = new Libs_Render('rooms');
        $stream         = '';
        $roomsList      = $this->_getRooms();

        $roomsTemplate->loop('rooms', $roomsList);
        $stream .= $roomsTemplate->render();

        $this->_display = $stream;
    }

    /**
     * return room with their options
     * @return array
     */
    protected function _getRooms()
    {
        $rooms      = array();
        $roomsList  = Libs_QueryModels::getRooms();
        foreach ($roomsList->result(1) as $room) {
            if (in_array($room['id'], $this->_lockedRooms)) {
                $room['locked'] = 'locked';
            } else {
                $room['locked'] = 'free';
            }
            $rooms[] = $room;
        }
        return $rooms;
    }

    /**
     * search for locked rooms
     * @param date $from
     * @param date $to
     */
    protected function _getLockedRooms($from, $to)
    {
        $currentTime    = strftime('%Y-%m-%d');
        $terms          = Libs_QueryModels::getTerms($currentTime);
        
        foreach ($terms->result(1) as $room) {
            $this->_checkIsLocked($room, $from, $to);
        }
    }

    /**
     * check that room is locked in given date range
     * @param array $room
     * @param date $from
     * @param date $to
     */
    protected function _checkIsLocked(array $room, $from, $to)
    {
        $bool = $this->compare(
            $room['data_przyjazdu'],
            $room['data_wyjazdu'],
            $from,
            $to
        );
        
        if (!$bool) {
            $this->_lockedRooms[] = $room['id_pokoje'];
        }
    }

    /**
     * check that send parameters are correct
     * @param date $from
     * @param date $to
     * @throws Exception
     */
    protected function _checkRange($from, $to)
    {
        $fromTimestamp = strtotime($from);
        $toTimestamp   = strtotime($to);

        if (!is_int($fromTimestamp) || !is_int($toTimestamp)) {
            throw new Exception ('Dates are not integer values');
        }
        
        $difference = $toTimestamp - $fromTimestamp;
        if ($difference < (3600 * 24)) {
            throw new Exception('To small difference between dates');
        }
    }

    /**
     * compare dates, and return boolean TRUE if room is available
     * @param date $roomFrom
     * @param date $roomTo
     * @param date $from
     * @param date $to
     * @return bool
     */
    protected function compare($roomFrom, $roomTo, $from, $to)
    {
        $roomAvailable = TRUE;
        //      ----
        //    ----
        if ($from < $roomFrom && $roomFrom < $to && $to < $roomTo) {
            $roomAvailable = FALSE;
        }
        //      ----
        //        ----
        if ($roomFrom < $from && $from < $roomTo && $roomFrom < $to) {
            $roomAvailable = FALSE;
        }
        //      ----
        //     ------
        if ($from < $roomFrom && $roomTo < $to && $roomFrom < $to) {
            $roomAvailable = FALSE;
        }
        //      ----
        //       --
        if ($roomFrom < $from && $from < $roomTo && $to < $roomTo) {
            $roomAvailable = FALSE;
        }
        //      ----
        //      ----
        if ($roomFrom == $from && $roomTo == $to) {
            $roomAvailable = FALSE;
        }
        //      ----
        //      ---
        if ($roomFrom == $from && $from < $roomTo && $to < $roomTo) {
            $roomAvailable = FALSE;
        }
        //      ----
        //       ---
        if ($roomFrom < $from && $from < $roomTo && $to == $roomTo) {
            $roomAvailable = FALSE;
        }
        //      ----
        //     -----
        if ($from < $roomFrom && $roomFrom < $to && $roomTo == $to) {
            $roomAvailable = FALSE;
        }
        //      ----
        //      -----
        if ($roomFrom == $from && $from < $roomTo && $roomTo < $to) {
            $roomAvailable = FALSE;
        }
        return $roomAvailable;
    }
}
