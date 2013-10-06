<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 1.2.0
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
     * contains all reserved rooms and their details to display
     * @var array
     */
    protected $_roomsDetails = array();

    /**
     * contains final calculated price of reserved rooms
     * @var int
     */
    protected $_finalPrice = 0;

    /**
     * contains number of reservation days
     * @var int
     */
    protected $_daysRange = 0;

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
            throw new Exception($xml->err);
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
                $this->_calculateDays($_POST['from'], $_POST['to']);
                $this->_calculatePrice($_POST['selectedRooms']);
                break;

            case 'personal':
                $this->_getUserForm();
                break;

            case 'submit':
                $this->_calculateDays($_POST['from'], $_POST['to']);
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
     * calculate number of days between dates
     * @param string $from
     * @param string $to
     */
    protected function _calculateDays($from, $to)
    {
        $fromTimestamp = strtotime($from);
        $toTimestamp   = strtotime($to);
        $daysTimestamp = $toTimestamp - $fromTimestamp;

        $this->_daysRange = $daysTimestamp /60/60/24;
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
        $userEmail  = $this->_prepareUserEmail();
        $adminEmail = $this->_prepareAdminEmail();

        $mailer = new PHPMailer();
        $mailer->Encoding = "8bit";
        $mailer->CharSet = "UTF-8";
        $mailer->AddReplyTo($this->_options['mail'], $this->_options['system_name']);
        $mailer->SetFrom($this->_options['mail'], $this->_options['system_name']);
        $mailer->AddAddress($_POST['data'][7]['value']);
        $mailer->Subject = 'Rezerwacja w Hotelu Arka';
        $mailer->MsgHTML($userEmail);

        if(!$mailer->Send()) {
            throw new Exception(
                'Błąd podczas wysyłania maila do użytkownika.
                 Skontaktuj się z obsługą hotelu aby potwierdić rezerwację.
                 <br/><br/>' . $mailer->ErrorInfo
            );
        }

        $mailer = new PHPMailer();
        $mailer->Encoding = "8bit";
        $mailer->CharSet = "UTF-8";
        $mailer->AddReplyTo($this->_options['mail'], $this->_options['system_name']);
        $mailer->SetFrom($this->_options['mail'], $this->_options['system_name']);
        $mailer->AddAddress($this->_options['mail']);
        $mailer->Subject = 'Nowa rezerwacja: ' . $_POST['from'] . ' - ' . $_POST['to'];
        $mailer->MsgHTML($adminEmail);

        if(!$mailer->Send()) {
            throw new Exception(
                'Błąd podczas wysyłania maila do użytkownika.
                 Skontaktuj się z obsługą hotelu aby potwierdić rezerwację.
                 <br/><br/>' . $mailer->ErrorInfo
            );
        }
    }

    /**
     * prepare data for user email, and create email template
     * return rendered template for user email
     * 
     * @return string
     */
    protected function _prepareUserEmail()
    {
        $userEmail = new Libs_Render('user_email');

        $userEmail->generate('system_name', $this->_options['system_name']);
        $userEmail->generate('system_name2', $this->_options['system_name2']);
        $userEmail->generate('term', $_POST['from'] . ' - ' . $_POST['to']);

        $counter        = 0;
        $tableClass     = 0;

        foreach ($_POST['rooms'] as $key => $room) {

            if ($key === 'promotion') {
                continue;
            }

            $roomPrice      = 0;
            $roomQuery      = Libs_QueryModels::getRooms($room['roomId']);
            $roomDetails    = $roomQuery->result();
            $roomPriceModel = $this->_createPriceModel(
                $room['roomId'],
                $room['roomSpace']
            );

            if ($tableClass) {
                $tableClassName = 'color';
                $tableClass     = FALSE;
            } else {
                $tableClassName = '';
                $tableClass     = TRUE;
            }

            if ($room['spa']) {
                $roomPrice  += $roomPriceModel['spa'];
                $room['spa'] = 'Tak';
            } else {
                $roomPrice  += $roomPriceModel['normal'];
                $room['spa'] = 'Nie';
            }

            if ($room['dostawka']) {
                $roomPrice += $roomPriceModel['dostawka'][$room['dostawka']];
                $dostawka   = $roomPriceModel['dostawka'][$room['dostawka']];
            } else {
                $dostawka = '';
            }

            $this->_finalPrice += $roomPrice;

            $this->_roomsDetails[$room['roomId']] = array(
                'counter'           => ++$counter,
                'room_number'       => $roomDetails['number'],
                'spa'               => $room['spa'],
                'reserved_space'    => $room['roomSpace'],
                'description'       => $roomDetails['description'],
                'floor'             => $roomDetails['floor'],
                'dostawka'          => $dostawka,
                'room_price'        => $roomPrice * $this->_daysRange,
                'class'             => $tableClassName,
            );
        }

        $finalPrice = $this->_finalPrice * $this->_daysRange;
        $promotion  = $this->_getPromotion();
        $userEmail->generate('full_base_price', $finalPrice);

        if ($promotion) {
            $userEmail->generate('promotion', $promotion);
            $percent    = $this->_percent($promotion, $finalPrice);
            $finalPrice -= $percent;
        }

        $userEmail->loop('rooms', $this->_roomsDetails);
        $userEmail->generate('price_sum', $finalPrice);

        return $userEmail->render();
    }

    /**
     * prepare data for administration email, and create email template
     * 
     * @return string
     */
    protected function _prepareAdminEmail()
    {
        $adminEmail = new Libs_Render('admin_email');

        $adminEmail->generate('term', $_POST['from'] . ' - ' . $_POST['to']);
        $adminEmail->loop('rooms', $this->_roomsDetails);

        $finalPrice = $this->_finalPrice * $this->_daysRange;
        $promotion  = $this->_getPromotion();
        $adminEmail->generate('full_base_price', $finalPrice);

        if ($promotion) {
            $adminEmail->generate('promotion', $promotion);
            $percent    = $this->_percent($promotion, $finalPrice);
            $finalPrice -= $percent;
        }

        $adminEmail->generate('price_sum', $finalPrice);

        foreach ($_POST['data'] as $information) {
            $adminEmail->generate($information['name'], $information['value']);
        }

        return $adminEmail->render();
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
            $promotion = $this->_getPromotion();
            if ($promotion) {
                $_POST['rooms']['promotion'] = $promotion;
            }
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
            Libs_QueryModels::removeTerms($reservationId);

            throw new Exception('Błąd podczas zapisu do bazy danych');
        }
    }

    /**
     * show information about success or fail saving reservation
     */
    protected function _showInfo()
    {
        $successTemplate = new Libs_Render('success');

        $successTemplate->loop('rooms', $this->_roomsDetails);

        $fullPrice = $this->_priceSum * $this->_daysRange;
        $successTemplate->generate('full_base_price', $fullPrice);

        $promotion = $this->_getPromotion();
        if ($promotion) {
            $successTemplate->generate('promotion', $promotion);
            $percent    = $this->_percent($promotion, $fullPrice);
            $fullPrice -= $percent;
        }

        $successTemplate->generate('final_price', $fullPrice);
        $successTemplate->generate('term', $_POST['from'] . ' - ' . $_POST['to']);

        $this->_display = $successTemplate->render();
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
     * @throws Exception
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
        } else {
            throw new Exception('Brak wybranych pokoi');
        }

        $fullPriceLayout = new Libs_Render('payment');
        $fullPriceLayout->generate('rooms', $priceStream);

        $fullPrice = $this->_priceSum * $this->_daysRange;
        $fullPriceLayout->generate('full_base_price', $fullPrice);

        $promotion = $this->_getPromotion();
        if ($promotion) {
            $fullPriceLayout->generate('promotion', $promotion);
            $percent    = $this->_percent($promotion, $fullPrice);
            $fullPrice -= $percent;
        }

        $fullPriceLayout->generate('full_price', $fullPrice);

        $this->_display = $fullPriceLayout->render();
    }

    /**
     * calculate percent form value
     *
     * @param float $part value that will be percent of other value
     * @param float $all value from calculate percent
     * @return integer
     */
    protected function _percent($part, $all)
    {
        return ($part / 100) *$all;
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
     * get promotion value for days range
     * 
     * @return null|integer
     */
    protected function _getPromotion()
    {
        $result = Libs_QueryModels::getPromotion($this->_daysRange)->result();
        if (!$result->err || count($result) > 0) {
            return $result['percent'];
        }

        return NULL;
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
        $lockedRooms    = $terms->result(1);

        if ($lockedRooms) {
            foreach ($lockedRooms as $room) {
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
