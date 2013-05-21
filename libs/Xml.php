<?php
/**
 * read/write xml files
 * @author chajr <chajr@bluetree.pl>
 * @version 2.2.0
 * @access public
 * @copyright chajr/bluetree
 * @package core
 */
class Libs_Xml extends DOMDocument{
    /**
     * xml root
     * @var xmlobject
     */
    public $documentElement;
    /**
     * node name
     * @var string
     */
    public $nodeName;
    /**
     * node type
     * ELEMENT_NODE					(1) element
     * ATTRIBUTE_NODE				(2) attribute
     * TEXT_NODE					(3) text node (elemnt or attribute)
     * CDATA_SECTION_NODE			(4) CDATA section
     * ENTITY_REFERENCE_NODE		(5) entity reference
     * ENTITY_NODE					(6) entity
     * PROCESSING_INSTRUCTION_NODE	(7) processing instruction
     * COMMENT_NODE					(8) comment
     * DOCUMENT_NODE				(9) document ( all xml document, root element)
     * @var integer
     */
    public $nodeType;
    /**
     * node value
     * @var mixed
     */
    public $nodeValue;
    /**
     * node parrent
     * @var xmlobject
     */
    public $parentNode;
    /**
     * collection of child nodes
     * @var xmlobject
     */
    public $childNodes;
    /**
     * first child
     * @var xmlobject
     */
    public $firstChild;
    /**
     * last child
     * @var xmlobject
     */
    public $lastChild;
    /**
     * collection of node attributes
     * @var xmlobject
     */
    public $attributes;
    /**
     * next node in collection
     * @var xmlobject
     */
    public $nextSibling;
    /**
     * previous node in collection
     * @var xmlobject
     */
    public $previousSibling;
    /**
     * node namespace
     */
    public $namespaceURI;
    /**
     * document object of reference node
     * @var xmlobject
     */
    public $ownerDocument;
    /**
     * number of elements in collection
     * @var integer
     */
    public $length;
    /**
     * DTD, if return document type as documentType
     * @var xmlobject
     */
    public $doctype;
    /**
     * document content implemantation way, compatible with document mime-type
     */
    public $implementation;
    /**
     * error code
     * @var string
     */
    public $err = NULL;
    /**
     * last free id
     * @var string
     */
    public $idList;
    /**
     * runs DOMDocument constructor, optionalu create new xml
     * @param float $version xml version
     * @param string $encoding xml encode
     * @uses DOMDocument::__construct()
     * @example new Package_Xml('1.0', 'utf-8');
     */
    public function __construct($version = '', $encoding = '')
    {
        if (class_exists('Test_Tracer')) {
            Test_Tracer::marker(array('creating xml object', debug_backtrace()));
        }
        parent::__construct($version, $encoding);
    }
    /**
     * load xml file, optionaly check it with DTD
     * @example loadFile('cfg/config.xml', 1)
     * @example loadFile('cfg/config.xml')
     * @param string $url xml file path
     * @param boolean $parse if true check file DTD
     * @return boolean if true, document was loaded correctly
     * @uses DOMDocument::$preserveWhiteSpace
     * @uses Package_Xml::$err
     * @uses DOMDocument::load()
     * @uses DOMDocument::validate()
     * @errors dont_exist, loading, parse
     */
    public function loadFile($url, $parse = FALSE)
    {
        if (class_exists('Test_Tracer')) {
            Test_Tracer::marker(array('loading xml file', debug_backtrace()));
        }
        $this->preserveWhiteSpace = FALSE;
        $bool = @file_exists($url);
        if (!$bool) {
            $this->err = 'dont_exist';
            return FALSE;
        }
        $bool = $this->load($url);
        if (!$bool) {
            $this->err = 'loading';
            return FALSE;
        }
        if ($parse && !@$this->validate()) {
            $this->err = 'parse';
            return FALSE;
        }
        return TRUE;
    }
    /**
     * save xml file, optionaly return it as string
     * @example saveFile('sciezka/plik.xml')
     * @example saveFile('sciezka/plik.xml', 1) save and return as text
     * @example saveFile(0, 1) return only as text
     * @param string $url path to xml
     * @param boolean $asText if true return xml as string
     * @return mixed save information, or xml content
     * @uses DOMDocument::$formatOutput
     * @uses Package_Xml::$err
     * @uses DOMDocument::save()
     * @uses DOMDocument::saveXML()
     * @errors saving
     */
    public function saveFile($url, $asText = FALSE)
    {
        if (class_exists('Test_Tracer')) {
            Test_Tracer::marker(array('saving xml file', debug_backtrace()));
        }
        $this->formatOutput = TRUE;
        if ($url) {
            $bool = $this->save($url);
            if(!$bool){
                $this->err = 'saving';
                return FALSE;
            }
        }
        if ($asText) {
            $data = $this->saveXML();
            return $data;
        }
        return TRUE;
    }
    /**
     * generates free numeric id
     * @param string $url scierzka do pliku
     * @param boolean $as_text czy ma zwrucic tresc xml-a
     * @return integer zwraca id, lub 0 jesli nie odanleziono wezlow
     * @uses DOMDocument::$documentElement
     * @uses DOMDocument::$childNodes
     * @uses Package_Xml::$idList
     * @uses Package_Xml::_search()
     */
    public function freeId()
    {
        if (class_exists('Test_Tracer')) {
            Test_Tracer::marker(array('checks free id in xml file', debug_backtrace()));
        }
        $root = $this->documentElement;
        if (!$root->hasChildNodes()) {
            return 0;
        } else {
            $childArray = array();
            $childArray = $this->_search($root->childNodes, $childArray, 'id');
            $childArray[] = 'create_new_free_id';
            $id = array_keys($childArray, 'create_new_free_id');
            unset($childArray);
            $this->idList = $id;
            return $id[0];
        }
    }
    /**
     * check document that element with given id exists
     * @param string $id id to search
     * @uses DOMDocument::getElementById()
     * @return boolean true if exist, else false
     */
    public function checkId($id)
    {
        if (class_exists('Test_Tracer')) {
            Test_Tracer::marker(array('checks that id exists', debug_backtrace()));
        }
        $id = $this->getElementById($id);
        if ($id) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    /**
     * alias of getElementById
     * @param string $id id to search
     * @return xmlobject return xml element with given id value
     * @uses DOMDocument::getElementById()
     */
    public function getId($id)
    {
        Test_Tracer::marker(array('getting id', debug_backtrace()));
        $id = $this->getElementById($id);
        return $id;
    }
    /**
     * search node witch added attribute value
     * @param xmlobject $childNodes xml elemnt to serach off
     * @param array $childArray child array
     * @param string $name attribut name to check (normaly 'id')
     * @return array
     * @uses DOMDocument::$nodeType
     * @uses DOMDocument::$childNodes
     * @uses DOMDocument::hasChildNodes()
     * @uses DOMDocument::getAttribute()
     * @uses Package_Xml::_search()
     */
    protected function _search($childNodes, $childArray, $name)
    {
        if (class_exists('Test_Tracer')) {
            Test_Tracer::marker(array('searching node', debug_backtrace()));
        }
        foreach ($childNodes as $child) {
            if ($child->nodeType === 1) {
                if ($child->hasChildNodes()) {
                    $childArray = $this->_search($child->childNodes, $childArray, $name);
                }
                $id = $child->getAttribute($name);
                if ($id) {
                    $childArray[$id] = $id;
                }
            }
        }
        return $childArray;
    }
}