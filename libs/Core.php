<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.2.0
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
        $header = new Libs_Render('header');
        $breadcrumbs = new Libs_Render('breadcrumbs');
        $footer = new Libs_Render('footer');
        $stream = '';
        $stream .= $header->render();
        $stream .= $breadcrumbs->render();
        //$stream .=  $core->display();
        $stream .=  $footer->render();
        $this->_display = $stream;
    }

    /**
     * return rendered content
     */
    public function display()
    {
        return $this->_display;
    }
}