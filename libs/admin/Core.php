<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package admin
 * @version 0.1.0
 * @copyright chajr/bluetree
 */
class Libs_Admin_Core
    extends Libs_Core
{
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
        switch ($_GET['page']) {
            default:
                $this->_baseRender();
                break;
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

        $stream = '';
        $stream .= $header->render();
        $stream .= $index->render();
        $stream .= $footer->render();

        $this->_display = $stream;
    }
}