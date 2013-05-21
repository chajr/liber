<?php
/**
* tworzy polaczenie z baza danych i przekazuje je do obiektu obslugo bazy danych
* @author chajr <chajr@bluetree.pl>
* @package db
* @version 1.3.0
* @copyright chajr/bluetree
* @final
* Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
* @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
*/
final class Libs_Connection 
    extends mysqli
{
    /**
    * informacja o bledzie polaczenia
    * @var string
    */
    public $err;
    /**
    * przechowuje tablice polaczen (default domyslne)
    * @var array
    */
    static $connections = array();
    /**
    * domyslne kodowanie dla zapytan
    * @var string
    */
    static $defaultCharset = 'UTF8';
    /**
    * tworzy instancje obiektu mysqli i dokonuje polaczenie z baza danch
    * NAZWA default JEST UZYWANA DLA DOMYSLNEGO POLACZENIA Z BAZA DANYCH!!!!
    * @param array $config tablica parametrow (host, user, password, db_name, connection)
    * @param string $charset nazwa kodowa dla zestawu znakow (domyslnie UTF8)
    * @return boolean jesli wystapil blad w placzeniu zwraca FALSE i informacje o bledzie w wlasciwosci $err
    * @example new mysql_connection_class(array(host=>'localhost', user=>'user', password=>'qw4@#$', db_name=>'baza', connection=>'nowe_polaczenie'))
    * @uses Libs_Connection::$err
    * @uses Libs_Connection::$connections
    * @uses Libs_Connection::$defaultCharset
    * @uses mysqli::__construct()
    * @uses mysqli::query()
    */
    public final function __construct(array $config, $charset = 'UTF8')
    {
        self::$defaultCharset = $charset;
        if (isset($config) && !empty($config)) {
            parent::__construct(
                $config['host'],
                $config['user'],
                $config['password'],
                $config['db_name']
            );
            if (mysqli_connect_error()) {
                $this->err = mysqli_connect_error();
                return FALSE;
            }
            $this->query("SET NAMES '$charset'");
        }
        if (!isset($config['connection']) || !$config['connection']) {
        $config['connection'] = 'default';
        }
        self::$connections[$config['connection']] = $this;
    }
    /**
    * niszczy wszystkie polaczenia
    * @uses mysql_connection_class::$connections
    */
    public final function __destruct()
    {
        self::$connections = array();
    }
    /**
    * niszczy wszystkie polaczenia, lub wybrane polaczenia
    * @param mixed $conn_array tablica polaczen do zniszczenia, lub nazwa polaczenia
    * @example destruct()
    * @example destruct('default')
    * @example destruct(array('polaczenie1', 'polaczenie2'))
    * @uses mysql_connection_class::$connections
    */
    static function destruct($conn_array = NULL)
    {
        if ($conn_array) {
            if(is_array($conn_array)){
                foreach ($conn_array as $connection) {
                    unset(self::$connections[$connection]);
                }
            } else {
                unset(self::$connections[$conn_array]);
            }
        } else {
            self::$connections = array();
        }
    }
}