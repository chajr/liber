<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package admin
 * @version 0.7.0
 * @copyright chajr/bluetree
 */
class Libs_Admin_Core
    extends Libs_Core
{
    /**
     * contains some information to render
     * @var string
     */
    protected $_information = '';

    /**
     * contains some information about error to render
     * @var string
     */
    protected $_ok = '';

    /**
     * contains some information about success to render
     * @var string
     */
    protected $_error = '';

    /**
     * starts Libs_Core
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * override controller, to display admin panel
     */
    protected function _controller()
    {
        $this->_checkLoginData();
        $isLoggedIn = $this->_checkIsLogged();

        if ($isLoggedIn) {
            switch ($_GET['page']) {
                case 'log_out':
                    $this->_logOut();
                    break;

                case 'remove_term':
                    $this->_removeTermAndReservation();
                    $this->_baseRender();
                    break;

                default:
                    $this->_baseRender();
                    break;
            }
        }
    }

    /**
     * render base page for admin
     */
    protected function _baseRender()
    {
        $header         = new Libs_Render('manager_top');
        $footer         = new Libs_Render('manager_bottom');
        $index          = new Libs_Render('manager_index');
        $menu           = new Libs_Render('manager_menu');

        $this->_setAdditionalInformation($header);
        $menu->generate('active_terms', 'active');
        $header->generate('nav_bar', $menu->render());
        $index->loop('terms', $this->_getTermsList());
        $index->loop('room_details', $this->_getRoomsDetails());

        $stream = '';
        $stream .= $header->render();
        $stream .= $index->render();
        $stream .= $footer->render();

        $this->_display = $stream;
    }

    /**
     * render page with log in form
     */
    protected function _renderLogInPage()
    {
        $header         = new Libs_Render('manager_top');
        $footer         = new Libs_Render('manager_bottom');
        $login          = new Libs_Render('manager_login');

        $this->_setAdditionalInformation($header);

        $stream  = '';
        $stream .= $header->render();
        $stream .= $login->render();
        $stream .= $footer->render();

        $this->_display = $stream;
    }

    /**
     * log off user and show login page with information
     */
    protected function _logOut()
    {
        Libs_Admin_Loger::logOff();
        $this->_ok = 'Zostałeś poprawnie wylogowany';
        $this->_renderLogInPage();
    }

    /**
     * remove term and reservations that are related
     */
    protected function _removeTermAndReservation()
    {
        $reservationId = $_GET['id'];
        Libs_QueryModels::removeTerm($reservationId);
        Libs_QueryModels::removeReservation($reservationId);
    }

    /**
     * set some additional information, errors or success information to show
     * 
     * @param Libs_Render $header
     */
    protected function _setAdditionalInformation(Libs_Render $header)
    {
        if ($this->_ok) {
            $header->generate('ok', $this->_ok);
        }

        if ($this->_error) {
            $header->generate('error', $this->_error);
        }

        if ($this->_information) {
            $header->generate('information', $this->_information);
        }
    }

    /**
     * check if user is logged in, if yes return TRUE, if not render log in page
     * 
     * @return bool
     */
    protected function _checkIsLogged()
    {
        $verification = Libs_Admin_Loger::verifyUser();

        if (!$verification) {
            $this->_renderLogInPage();
            return FALSE;
        }

        return TRUE;
    }

    /**
     * check that password was send, and if send try to log in user
     */
    protected function _checkLoginData()
    {
        if (isset($_POST['pass'])) {
            $encryptedPass  = hash('sha256', $_POST['pass']);
            $admin          = Libs_Admin_QueryModels::getAdmin($encryptedPass);
            $adminData      = $admin->result();

            if ($adminData) {
                $logInNumber = $adminData['admin_lognum']++;
                $currentDate = date("Y-m-d H:i:s", time());
                $bool        = Libs_Admin_QueryModels::setLogInAdmin(
                    $logInNumber,
                    $currentDate,
                    $adminData['admin_id']
                );

                if ($bool) {
                    Libs_Admin_Loger::logOn(
                        $adminData['admin_id'],
                        1,
                        $adminData['admin_groups_id']
                    );

                    $this->_ok = 'Zostałeś zalogowany poprawnie';
                } else {
                    $this->_error = 'Niemożliwe zapisanie danych logowania,
                     spróbuj jeszcze raz';
                }
            } else {
                $this->_error = 'Użytkownik nie istnieje, lub źle podane hasło';
            }
        }
    }

    /**
     * create array of terms with some special options to display on term list page
     * 
     * @return array
     */
    protected function _getTermsList()
    {
        $terms      = Libs_Admin_QueryModels::getTerms();
        $fullTerms  = array();

        if ($terms->err) {
            $this->_error = $terms->err;
        } else {
            $termsData = $terms->result(TRUE);

            foreach ($termsData as $index => $term) {
                $roomData           = $this->_getRoomData($term['id_pokoje']);
                $reservationData    = $this->_getReservedRoomOption(
                    $term['id_reservation'],
                    $term['id_pokoje']
                );
                $roomSpace = $this->_calculateDostawka($reservationData);

                $fullTerms[$index]  = array(
                    'id'                => $term['id'],
                    'id_reservation'    => $term['id_reservation'],
                    'room_space'        => $roomSpace,
                    'room_number'       => $roomData['number'],
                    'from'              => $term['data_przyjazdu'],
                    'to'                => $term['data_wyjazdu'],
                    'class'             => $this->_getRoomClass(
                        $term['data_przyjazdu'],
                        $term['data_wyjazdu']
                    ),
                );
            }
        }

        return $fullTerms;
    }

    /**
     * get full data of given room id
     * 
     * @param integer $roomId
     * @return array
     */
    protected function _getRoomData($roomId)
    {
        $room = Libs_QueryModels::getRooms($roomId);

        return $room->result();
    }

    /**
     * get all options for given reservation id and room
     * 
     * @param integer $reservationId
     * @param integer $roomId
     * @return array|null
     */
    protected function _getReservedRoomOption($reservationId, $roomId)
    {
        $reservedSpace      = Libs_Admin_QueryModels::getReservations($reservationId);
        $reservationData    = $reservedSpace->result();
        $reservationOptions = unserialize($reservationData['opcje']);

        if ($reservationOptions) {
            return $this->_getRoomOption($reservationOptions, $roomId);
        }

        return NULL;
    }

    /**
     * get option for given room, from reservation option list
     * 
     * @param array $reservationOptions
     * @param integer $roomId
     * @return null|array
     */
    protected function _getRoomOption(array $reservationOptions, $roomId)
    {
        foreach ($reservationOptions as $option) {
            if ($option['roomId'] === $roomId) {
                return $option;
            }
        }

        return NULL;
    }

    /**
     * return room space with dostawka if their exist
     * 
     * @param array $options
     * @return integer
     */
    protected function _calculateDostawka($options)
    {
        if ($options['dostawka']) {
            return $options['roomSpace'] +1;
        }
        return $options['roomSpace'];
    }

    /**
     * check that reservation term is in future, on in past
     * and return an special bootstrap class to highlight row on term list
     * 
     * @param string $from
     * @param string $to
     * @return string bootstrap 3 table rows classes
     */
    protected function _getRoomClass($from, $to)
    {
        $currentTime    = time();
        $timeFrom       = strtotime($from);
        $timeTo         = strtotime($to);

        if ($timeTo < $currentTime) {
            return 'danger';
        }

        if ($timeFrom > $currentTime) {
            return 'success';
        }

        return '';
    }

    /**
     * create array of room details to be used in modal window for term list
     * 
     * @return array
     */
    protected function _getRoomsDetails()
    {
        $rooms          = Libs_Admin_QueryModels::getRoomsWithTerms();
        $roomsData      = $rooms->result(TRUE);
        $roomsDisplay   = array();

        foreach ($roomsData as $room) {
            $roomOptions = $this->_getRoomOption(
                unserialize($room['opcje']), $room['id']
            );
            $price          = $this->_calculatePriceForRoom($roomOptions);
            $roomsDisplay[] = array(
                'term_id'           => $room['term_id'],
                'room_number'       => $room['number'],
                'room_space'        => $roomOptions['roomSpace'],
                'dostawka'          => $this->_changeToString($roomOptions['dostawka']),
                'type'              => $this->_getDostawkaType($roomOptions['dostawka']),
                'spa'               => $this->_changeToString($roomOptions['spa']),
                'floor'             => $room['floor'],
                'price'             => $price,
                'room_description'  => $room['description']
            );
        }

        return $roomsDisplay;
    }

    /**
     * change dostawka number value to description
     * 
     * @param integer $dostawka
     * @return string
     */
    protected function _getDostawkaType($dostawka)
    {
        $typeString = '';
        switch ($dostawka) {
            case 1:
                $typeString = ' - dla osoby dorosłej';
                break;

            case 2:
                $typeString = ' - dla dziecka 10 – 18 lat';
                break;

            case 3:
                $typeString = ' - dla dziecka 3 - 10 lat';
                break;

            default:
                break;
        }

        return $typeString;
    }

    /**
     * change dostawka number value to string
     * 
     * @param interger|string $value
     * @return string
     */
    protected function _changeToString($value)
    {
        if ($value) {
            return 'Tak';
        }
        return 'Nie';
    }

    /**
     * calculate final room price for given reservation details
     * 
     * @param array $roomOptions
     * @return float|integer
     */
    protected function _calculatePriceForRoom(array $roomOptions)
    {
        $roomPrice      = 0;
        $roomPriceModel = $this->_createPriceModel
            ($roomOptions['roomId'],
                $roomOptions['roomSpace']
            );

        if ($roomOptions['dostawka'] === '') {
            $roomOptions['dostawka'] = 0;
        }

        if ($roomOptions['spa']) {
            $roomPrice  += $roomPriceModel['spa'];
        } else {
            $roomPrice  += $roomPriceModel['normal'];
        }

        if ($roomOptions['dostawka']) {
            $roomPrice += $roomPriceModel['dostawka'][$roomOptions['dostawka']];
        }
        return $roomPrice;
    }

    protected function _getReservationList()
    {
        
    }

    protected function _getReservation($id)
    {
        
    }
}
