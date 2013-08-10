<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.11.1
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
     * contains calculated price value
     * @var float
     */
    protected $_priceSum;

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

            case 'payment':
                $this->_calculatePrice($_POST['selectedRooms']);
                break;

            case 'personal':
                $this->_getUserForm();
                break;

            case 'submit':
                $this->_validateForm();
                $reservationId = $this->_saveReservation();
                $this->_saveTerm($reservationId);
                $this->_sendInfo();
                $this->_showInfo();
                break;

            default:
                $this->_baseRender();
                break;
        }
    }

    /**
     * check that form filed are filled correctly
     * @throws Exception
     */
    protected function _validateForm()
    {
        $validFlag = array();

        if (!isset($_POST['rooms']) || empty($_POST['rooms'])) {
            throw new Exception('Brak wybranych pokojów');
        }

        foreach ($_POST['data'] as $input) {
            $value = $input['value'];

            switch ($input['name']) {
                case'imie':
                    $bool = Libs_Valid::valid($value, 'letters');
                    if (!$bool) {
                        $validFlag['imie'] = ' _ ,.- oraz litery';
                    }
                    break;

                case'nazwisko':
                    $bool = Libs_Valid::valid($value, 'letters');
                    if (!$bool) {
                        $validFlag['nazwisko'] = ' _ ,.- oraz litery';
                    }
                    break;

                case'ulica':
                    $bool = Libs_Valid::valid($value, 'num_chars');
                    if (!$bool) {
                        $validFlag['ulica'] = '.,_- oraz litery i cyfry';
                    }
                    break;

                case'numer':
                    $bool = Libs_Valid::valid($value, 'multinum');
                    if (!$bool) {
                        $validFlag['multinum'] = ' _ ,.- oraz litery';
                    }
                    break;

                case'miasto':
                    $bool = Libs_Valid::valid($value, 'num_chars');
                    if (!$bool) {
                        $validFlag['ulica'] = '.,_- oraz litery i cyfry';
                    }
                    break;

                case'kod':
                    $bool = Libs_Valid::postCode($value);
                    if (!$bool) {
                        $validFlag['kod'] = 'format: xx-xxx';
                    }
                    break;

                case'telefon':
                    $bool = Libs_Valid::phone($value);
                    if (!$bool) {
                        $validFlag['telefon'] = 'np: +48 ( 052 ) 131 231-2312';
                    }
                    break;

                case'email':
                    $bool = Libs_Valid::mail($value);
                    if (!$bool) {
                        $validFlag['email'] = FALSE;
                    }
                    break;

                case'regulamin':
                    if ($value !== '1') {
                        $validFlag['regulamin'] = 'regulamin musi być zaznaczony';
                    }
                    break;

                default:
                    throw new Exception ('Nieoczekiwane dane - ' . $input['name']);
                    break;
            }
        }

        if (!empty($validFlag)) {
            $message = 'Błędnie wypełnione pola<br/>';
            
            foreach ($validFlag as $key => $error) {
                $message .= $key . ' - tylko znaki: ' . $error . '<br/>';
            }

            throw new Exception($message);
        }
    }

    /**
     * send emails to user and to hotel
     */
    protected function _sendInfo()
    {
        
    }

    /**
     * save reservation information to database
     * 
     * @return int|null reservation id if was created
     * @throws Exception
     */
    protected function _saveReservation()
    {
        if (isset($_POST['rooms']) && !empty($_POST['rooms'])) {
            $rooms = serialize($_POST['rooms']);
        } else {
            throw new Exception ('Brak danych dla pokoi');
        }

        $data = array();
        foreach ($_POST['data'] as $input) {
            $data[$input['name']] = $input['value'];
        }

        $reservation  = Libs_QueryModels::saveReservation(
            $data['imie'], $data['nazwisko'], $_POST['from'], $_POST['to'],
            $data['email'], $data['telefon'], $data['ulica'], $data['numer'],
            $data['miasto'], $data['kod'], $rooms
        );

        if (!$reservation->id) {
            throw new Exception('Błąd podczas zapisu do bazy danych');
        }

        return $reservation->id;
    }

    /**
     * save term information
     * 
     * @param integer $reservationId
     * @throws Exception
     */
    protected function _saveTerm($reservationId)
    {
        $errorFlag = FALSE;

        if (isset($_POST['rooms']) && !empty($_POST['rooms'])) {

            foreach ($_POST['rooms'] as $room) {
                $term  = Libs_QueryModels::saveTerm(
                    $room['roomId'], $reservationId, $_POST['from'], $_POST['to']
                );

                if (!$term->id) {
                    $errorFlag = TRUE;
                }
            }
        }

        if ($errorFlag) {
            Libs_QueryModels::removeReservation($reservationId);
            Libs_QueryModels::removeTerm($reservationId);

            throw new Exception('Błąd podczas zapisu do bazy danych');
        }
    }

    /**
     * show information about success or fail saving reservation
     */
    protected function _showInfo()
    {
        
    }

    /**
     * show form to submit reservation
     */
    protected function _getUserForm()
    {
        $userData = new Libs_Render('result_payment');
        
        $this->_display = $userData->render();
    }

    /**
     * calculate for selected rooms and render layout for it
     * @param array $selectedRooms
     */
    protected function _calculatePrice($selectedRooms)
    {
        $priceStream = '';
        if (!empty($selectedRooms)) {
            foreach ($selectedRooms as $room) {
                $finalPrice = $this->_createPriceModel(
                    $room['roomId'],
                    $room['roomSpace']
                );

                $priceStream .= $this->_renderPrice($finalPrice);
            }
        }

        $fullPriceLayout = new Libs_Render('payment');
        $fullPriceLayout->generate('rooms', $priceStream);
        $fullPriceLayout->generate('full_price', $this->_priceSum);
        $this->_display = $fullPriceLayout->render();
    }

    /**
     * render prices for single room
     * @param array $finalPrice
     * @return string
     */
    protected function _renderPrice($finalPrice)
    {
        $roomPriceLayout = new Libs_Render('room_price');
        $roomPriceLayout->generate('id', $finalPrice['id']);
        $roomPriceLayout->generate('room_name', $finalPrice['room_name']);
        $roomPriceLayout->generate('normal', $finalPrice['normal']);
        $roomPriceLayout->generate('spa', $finalPrice['spa']);

        if ($finalPrice['single']) {
            $roomPriceLayout->generate('single', '');
        }

        if ($finalPrice['dostawka']) {
            foreach ($finalPrice['dostawka'] as $key => $value) {
                $valueName  = 'dostawka_' . $key . '_value';
                $name       = 'dostawka_' . $key;
                $roomPriceLayout->generate($valueName, $key);
                $roomPriceLayout->generate($name, $value);
            }

            $roomPriceLayout->generate('dostawka_n', '');
        }

        return $roomPriceLayout->render();
    }

    /**
     * return calculated prices for one room
     * @param integer $roomId
     * @param integer $roomSpace
     * @return array
     */
    protected function _createPriceModel($roomId, $roomSpace)
    {
        $prices             = array();
        $prices['single']   = FALSE;
        $roomsData          = Libs_QueryModels::getRooms($roomId)->result();
        $priceModel         = $this->_getPriceModelData($roomsData['price_model']);

        if (isset($priceModel['one_price'])) {
            $prices['normal']   = $priceModel['one_price'];
            if ($priceModel['spa']) {
                $prices['spa']      = $priceModel['spa_1'];
            }
        } else {
            $prices['normal']   = $priceModel['price_' . $roomSpace];
            if ($priceModel['spa']) {
                $prices['spa']      = $priceModel['spa_' . $roomSpace];
            }
            
            if ((string)$roomSpace === '1') {
                $prices['single'] = TRUE;
            }
        }

        $this->_priceSum += $prices['normal'];

        $prices['dostawka']     = $this->_checkDostawka($priceModel);
        $prices['room_name']    = $roomsData['description'];
        $prices['id']           = $roomId;
        return $prices;
    }

    /**
     * check that dostawka is available
     * @param array $roomData
     * @return array|integer
     */
    protected function _checkDostawka($roomData)
    {
        $dostawka = array();
        if (isset($roomData['dostawka_1'])) {
            $dostawka[1] = $roomData['dostawka_1'];
        }

        if (isset($roomData['dostawka_1'])) {
            $dostawka[2] = $roomData['dostawka_2'];
        }

        if (isset($roomData['dostawka_1'])) {
            $dostawka[3] = $roomData['dostawka_3'];
        }

        if (empty($dostawka)) {
            return 0;
        }
        return $dostawka;
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
     * @param string $from
     * @param string $to
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
     * create list of options for room select
     * @param integer $spaceSize
     * @return string
     */
    protected function _createSpaceList($spaceSize)
    {
        $roomArray = array();
        for ($i = 1; $i <= $spaceSize; $i++) {
            $roomArray[] = array(
                'value' => $i
            );
        }

        $spaceTemplate  = new Libs_Render('room_space');
        $spaceTemplate->loop('space', $roomArray);
        return $spaceTemplate->render();
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
                $room['locked'] = 'lock';
            } else {
                $room['locked'] = 'unlock';
            }
            $room['space_option'] = $this->_createSpaceList($room['space']);
            $rooms[] = $room;
        }
        return $rooms;
    }

    /**
     * return price model for given serialized string, or single model value
     * @param string $data
     * @param integer|null $id
     * @return array|integer|bool
     */
    protected function _getPriceModelData($data, $id = NULL)
    {
        $array = unserialize($data);
        if ($id) {
            return $array[$id];
        }
        return $array;
    }

    /**
     * search for locked rooms
     * @param string $from
     * @param string $to
     */
    protected function _getLockedRooms($from, $to)
    {
        $currentTime    = strftime('%Y-%m-%d');
        $terms          = Libs_QueryModels::getTerms($currentTime);
        if ($terms->result(1)) {
            foreach ($terms->result(1) as $room) {
                $this->_checkIsLocked($room, $from, $to);
            }
        }
        
    }

    /**
     * check that room is locked in given date range
     * @param array $room
     * @param string $from
     * @param string $to
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
     * @param string $from
     * @param string $to
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
     * @param string $roomFrom
     * @param string $roomTo
     * @param string $from
     * @param string $to
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
