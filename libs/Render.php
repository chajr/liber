<?php
/**
 * klasa odpowiedzialana za wyswietlanie
 * przetwarzanie szablonow, zastepowanie znacznikow, petle, renderowanie pelnej stron, naprawa scierzek, wyswietlanie css i js
 *
 * okrojona wersja z blueframework
 *
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 1.1.2
 * @copyright chajr/bluetree
 */
class Libs_Render
{
    /**
     * przechowuje tablice z tresciami pochodzacymi z glownego layoutu oraz modulow
     * na koncu sklada tablice i do wlasciwosci zapisuje kompletna tresc
     * @var array
     */
    private $_DISPLAY = array('core' => '');
    /**
     * wyrazenie regularne odpowiadajace wszystkim znacznikom
     * @var string
     * @access private
     */
    private $_contentTags = "{;[\\w=\-|&();\/,]+;}";
    /**
     * laduje glowny layout oraz powiazane z nim pliki zewnetrzne
     * @param string $layout nazwa glownego layoutu do zaladowania, NULL jesli renderuje css/js
     * @uses Libs_Render::layout()
     */
    public function __construct($layout)
    {
        $this->layout($layout);
    }
    /**
     * umozliwia zastapienie znacznika trescia w danym module, lub grupy znacznikow tablica (gdzie klucz tablicy == znacznik)
     * @param mixed $znacznik nazwa znacznika do zastapienia, lub tablica znacznik => wartosc
     * @param string $tresc tresc do zastapienia znacznika, lub pusty kiedy przekazywany array
     * @param string $modul nazwa modulu zglaszajacego generowanie (core domyslnie)
     * @return integer zwraca ilosc zastapionych znacznikow, lub NULL jesli nie znaleziono elementow
     * @example generate('znacznik', 'jakas tres do wyswietlenia')
     * @example generate('znacznik', 'jakas tres do wyswietlenia', 'modul')
     * @example generate(array('znacznik1' => 'tresc', 'znacznik2' => 'inna tresc'), '')
     * @uses Libs_Render::$_DISPLAY
     */
    public function generate($znacznik, $tresc, $modul = 'core')
    {
        if (isset($this->_DISPLAY[$modul])) {
            $int = 0;
            if (!$tresc && is_array($znacznik)) {
                foreach ($znacznik as $element => $tresc) {
                    $this->_DISPLAY[$modul] = str_replace(
                        '{;'.$element.';}',
                        $tresc,
                        $this->_DISPLAY[$modul],
                        $int2
                    );
                    $int += $int2;
                }
            } else {
                $this->_DISPLAY[$modul] = str_replace(
                    '{;'.$znacznik.';}',
                    $tresc,
                    $this->_DISPLAY[$modul],
                    $int
                );
            }
            return $int;
        }
        return NULL;
    }
    /**
     * przetwarza tablice i generuje na jej podstawie odpowiednia tresc
     * @param string $znacznik znacznik do zastapienia
     * @param array $tablica dane do zapisania
     * @param string $modul opcjonalnie modul ktory zglasza tresc, inaczej zastepuje w szablonie glownym
     * @return integer zwraca ilosc zastapionych znacznikow, lub NULL jesli nie znaleziono elementow
     * @uses Libs_Render::$_DISPLAY
     * @example loop('jakis_znacznik', array(array(key => val), array(key2 => val2)), 'mod');
     * @example loop('jakis_znacznik', array(array(key => val), array(key2 => val2)));
     */
    public function loop($znacznik, $tablica, $modul = NULL)
    {
        if (!$modul) {
            $modul = 'core';
        }
        $int = NULL;
        if ($tablica) {
            $start  = '{;start;'.$znacznik.';}';
            $end    = '{;end;'.$znacznik.';}';
            $poz1   = strpos($this->_DISPLAY[$modul], $start);
            $poz1   = $poz1 + mb_strlen($start);
            $poz2   = strpos($this->_DISPLAY[$modul], $end);
            $poz2   = $poz2 - $poz1;
            if ($poz2 < 0) {
                return;
            }
            $szablon = substr($this->_DISPLAY[$modul], $poz1, $poz2);
            $end = '';
            $tmp = '';
            $int = 0;
            foreach ($tablica as $wiersz) {
                $tmp = $szablon;
                foreach ($wiersz as $klucz => $wartosc) {
                    $wzor = '{;'.$znacznik.';'.$klucz.';}';
                    $tmp = str_replace($wzor, $wartosc, $tmp);
                }
                $end .= $tmp;
            }
            $this->_DISPLAY[$modul] = str_replace(
                $szablon,
                $end,
                $this->_DISPLAY[$modul],
                $int2
            );
            $int += $int2;
            unset($end);
            unset($szablon);
            unset($tablica);
        }
        return $int;
    }
    /**
     * scala tresci zawarte w grupach modulow w kompletna strone, zastepuje sciezki,
     * naprawia linki, czysci i kompresuje, jesli debug wylaczony usuwa wszystko co zostalo wyswietlone
     * @return string kompletna zawartosc strony do wyswietlenia
     * @uses Libs_Render::$_DISPLAY
     * @uses Libs_Render::clean()
     */
    public function render()
    {
        $this->_DISPLAY = $this->_DISPLAY['core'];
        $this->clean();
        return $this->_DISPLAY;
    }
    /**
     * umozliwia zaladowanie layoutu do tablicy _DISPLAY (glowny, badz layouty dla modulow)
     * @param string $layout nazwa layoutu do zaladowania
     * @param string $mod nazwa moulu dla ktorego ladowany layout (jesli FALSE ladowany layout dla core)
     * @uses Libs_Render::$_DISPLAY
     * @example layout('nazwa_layoutu')
     * @example layout('nazwa_layoutu', 'mod')
     * @throws Exception core_error_2
     */
    public function layout($layout, $mod = 'core')
    {
        $path = BASE_PATH . "/templates/$layout.html";
        $this->_DISPLAY[$mod] = file_get_contents($path);
        if (!$this->_DISPLAY[$mod]) {
            throw new Exception('core_error_2 '. $mod.' - '.$path);
        }
    }
    /**
     * oczyszcza layout z niewykozystanych znacznikow
     * @uses Libs_Render::$_DISPLAY
     * @uses Libs_Render::clean_chk()
     * @uses Libs_Render::$_contentTags
     */
    private function clean()
    {
        $this->clean_chk('opt');
        $this->clean_chk('petla');
        $this->_DISPLAY = preg_replace(
            '#'.$this->_contentTags.'#',
            '',
            $this->_DISPLAY
        );
    }
    /**
     * oczyszcza layout z petli w ktorych znajduja sie nieobsluzone znaczniki,
     * badz niewykozystane znaczniki opcjonalne
     * @param string $typ typ do sprawdzenia
     * @uses Libs_Render::$tagi_tresc
     * @uses Libs_Render::$_DISPLAY
     */
    private function clean_chk($typ)
    {
        switch ($typ) {
            case'petla':
                $reg1 = '#{;(start|end);([\\w-])+;}#';
                $reg2 = '#{;([\\w-])+;([\\w-])+;}#';
                $reg3 = '{;start;';
                $reg4 = '{;end;';
                break;
            case'opt':
                $reg1 = '#{;op;([\\w-])+;}#';
                $reg2 = $this->_contentTags;
                $reg3 = '{;op;';
                $reg4 = '{;op_end;';
                break;
            default:
                return;
                break;
        }
        $bool = preg_match_all($reg1, $this->_DISPLAY, $tab);
        if (!empty($tab) && !empty($tab[0])) {
            foreach ($tab[0] as $znacznik) {
                $start          = strpos($this->_DISPLAY, $znacznik);
                $znacznik_end   = str_replace($reg3, $reg4, $znacznik);
                $end            = strpos($this->_DISPLAY, $znacznik_end);
                if (!$start || !$end) {
                    continue;
                }
                $start_content  = $start + mb_strlen($znacznik);
                $content_len    = $end - $start_content;
                $string         = substr($this->_DISPLAY, $start_content, $content_len);
                $len            = ($end += mb_strlen($znacznik_end)) - $start;
                $string_del     = substr($this->_DISPLAY, $start, $len);
                $bool           = preg_match($reg2, $string);
                if ($bool) {
                    $this->_DISPLAY = str_replace(
                        $string_del,
                        '',
                        $this->_DISPLAY
                    );
                } else {
                    $this->_DISPLAY = str_replace(
                        $string_del,
                        $string,
                        $this->_DISPLAY
                    );
                }
            }
        }
    }
}
