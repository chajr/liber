<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package admin
 * @version 0.4.0
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
        $header->generate('nav_bar', $menu->render());

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
}
