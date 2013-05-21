<?php
/**
 * klasa obslugujaca polaczenia i zapytania do bazy danych mysql
 * @author chajr <chajr@bluetree.pl>
 * @version 3.3.0
 * @copyright chajr/bluetree
 * @final
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
 */
final class Libs_Mysql
{
    /**
     * informacja o bledzie jesli wystapil
     * @var string 
     */
    public $err = NULL;
    /**
     * id ostatnio dodanego elelemntu
     * @var integer
     */
    public $id = NULL;
    /**
     * ilosc zwruconych przez zapytanie wierszy
     * @var integer 
     */
    public $rows = NULL;
    /**
     * nazwa wybranego polaczenia
     * @var string 
     */
    private $connection;
    /**
     * przechowuje obiekt pobranych danych z bazy
     * @var object
     */
    private $result;
    /**
     * ustawia domyslne polaczenie i wykonuje zapytanie
     * opcjonalnie mozna zmienic kodowanie dla pojedynczego zapytania
     * @param string $sql zapytanie do bazy danych
     * @param string $connection opcjonalnie nazwa polaczenia z jakiego ma kozystac (0-domyslny np. przy zmianie kodowania)
     * @param string $charset system kodowania znakow
     * @example new Libs_Mysql('SELECT * FROM tablica')
     * @example new Libs_Mysql('SELECT * FROM tablica', 'inne_polaczenie')
     * @example new Libs_Mysql('SELECT * FROM tablica', 0, 'LATIN1')
     * @uses Libs_Mysql::$connection
     * @uses Libs_Mysql::_query()
     * @uses Libs_Mysql::setNames()
     * @uses Libs_Connection::$default_charset
     */
    public function __construct($sql, $connection = 'default', $charset = NULL)
    {
        if (!$connection) {
            $this->connection = 'default';
        } else {
            $this->connection = $connection;
        }
        
        if ($charset) {
            $this->setNames($charset);
        }
        
        $this->_query($sql);
        if ($charset) {
            $this->setNames(Libs_Connection::$default_charset);
        }
    }
    /**
     * zwraca dane, przetworzone do tablicy ($full = 1 - wszystkie pobrane dane) lub jako wynik fetch_assoc() (pojedynczy wiersz)
     * @param boolean $full informacja czy ma zwracac dane do przetworzenia czy jako tablice
     * @return array tablica danych 
     * @uses Libs_Mysql::$ilosc_wierszy
     * @uses Libs_Mysql::$connections
     * @uses Libs_Mysql::$connection
     * @uses mysqli::fetch_assoc()
     */
    public function result($full = FALSE)
    {
        if ($this->rows) {
            if ($full) {
                $arr = array();
                while ($array = $this->result->fetch_assoc()) {
                    if (!$array) {
                        return NULL;
                    }
                    $arr[] = $array;
                }
            } else {
                $arr = $this->result->fetch_assoc();
            }
            return $arr;
        }
    }
    /**
     * zwraca obiekt pobranych danych (instancja mysqli result)
     * @return object pobranych danych
     * @uses Libs_Mysql::$result
     */
    public function returns()
    {
        return $this->result;
    }
    /**
     * koduje tresci da zapytania (NUL (ASCII 0), \n, \r, \, ', ", and Control-Z)
     * @param string $tresc treac do zakodowania
     * @return string zakodowana tresc
     */
    public final static function code($tresc)
    {
        $tresc = mysqli_real_escape_string($tresc);
        return $tresc;
    }
    /**
     * dodaje sekwencje ucieczki do zastrzezonych znakow (& ' " < >)
     * @param string $tresc treac do zakodowania
     * @return string zakodowana tresc
     */
    public final static function entities($tresc)
    {
        $tresc = @htmlspecialchars($tresc);
        return $tresc;
    }
    /**
     * usuwa sekwence sterujace z zastzrezonych znakow (& ' " < >)
     * @param string $tresc tresc do dekodowania
     * @return string zdekodowana tresc
     */
    public final static function decode($tresc)
    {
        $tresc = @stripcslashes($tresc);
        return $tresc;
    }
    /**
     * wykonuje zapytanie do bazy danych
     * @param string $sql zapytanie do bazy
     * @uses Libs_Mysql::$rows
     * @uses mysql_connection_class::$connections
     * @uses Libs_Mysql::$connection
     * @uses Libs_Mysql::$err
     * @uses Libs_Mysql::$result
     * @uses Libs_Mysql::$id
     * @uses mysqli::$error
     * @uses mysqli::$insert_id
     * @uses mysqli::$num_rows
     * @uses mysqli::query()
     */
    private function _query($sql)
    {
        $bool = Libs_Connection::$connections[$this->connection]->query($sql);
        if (!$bool) {
            $this->err = Libs_Connection::$connections[$this->connection]->error;
            return;
        }
        
        if (Libs_Connection::$connections[$this->connection]->insert_id) {
            $this->id = Libs_Connection::$connections[$this->connection]->insert_id;
        }
        
        if (!is_bool($bool) && !is_integer($bool)) {
            $this->rows = $bool->num_rows;
        }
        
        $this->result = $bool;
    }
    /**
     * zmiana kodowania znakow dla zapytania
     * @param string $charset system kodowania znakow
     * @uses Libs_Mysql::$connection
     * @uses Libs_Connection::$connections
     * @uses mysqli::query()
     */
    private function setNames($charset)
    {
        mysql_connection_class::$connections[$this->connection]->query(
            "SET NAMES '$charset'"
        );
    }
}
