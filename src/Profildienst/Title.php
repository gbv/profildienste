<?php

namespace Profildienst;

/**
 * Represents a title
 *
 * Class Title
 * @package Profildienst
 */
class Title {

    /**
     * @var array JSON Data of this title
     */
    private $j;

    /**
     * @var string name of the watchlist the title is in
     */
    private $wl_name;

    /**
     * @var string URL of the cover
     */
    private $cover;
    /**
     * @var string MAK
     */
    private $mak;
    /**
     * @var string ILNS
     */
    private $ilns;

    private static $lookup = array(
        'ersterfassung' => array('001A', '0'),
        'letze_aenderung_datum' => array('001B', '0'),
        'letze_aenderung_uhrzeit' => array('001B', 't'),
        'statusaenderung_datum' => array('001D', '0'),
        'gattung' => array('002@', '0'),
        'isbn10' => array('004A', '0'),
        'isbn13' => array('004A', 'A'),
        'lieferbedingungen_preis' => array('004A', 'f'),
        'kommentar_lieferbedingungen_preis' => array('004A', 'm'),
        'kommentar_isbn' => array('004A', 'c'),
        'ean' => array('004L', '0'),
        'dnb_nummer' => array('006L', '0'),
        'verbund_id_num' => array('006L', '0'),
        'verbund_id_num_vortext' => array('006L', 'c'),
        'wv_dnb' => array('006U', '0'),
        'id_ersterfasser_vortext' => array('007G', 'c'),
        'id_ersterfasser' => array('007G', '0'),
        'addr_erg_ang_url' => array('009Q', 'a'),
        'addr_erg_ang_mime' => array('009Q', 'q'),
        'addr_erg_ang_bezw' => array('009Q', '3'),
        'sprachcode' => array('010@', 'a'),
        'erscheinungsjahr' => array('011@', 'a'),
        'code_erscheinungsland' => array('019@', 'a'),
        'titel' => array('mult' => true, 'values' => array(array('021A', 'a'), array('021B', 'a'))),
        'untertitel' => array('mult' => true, 'values' => array(array('021A', 'd'), array('021B', 'd'), array('021A', 'l'), array('021B', 'l'))),
        'verfasser' => array('mult' => true, 'values' => array(array('021A', 'h'), array('021B', 'h'))),
        'verfasser_vorname' => array('028A', 'd'),
        'verfasser_nachname' => array('028A', 'a'),
        'ort' => array('033A', 'p'),
        'verlag' => array('033A', 'n'),
        'umfang' => array('034D', 'a'),
        'format' => array('mult' => true, 'values' => array(array('033I', 'a'), array('034I', 'a'))),
        'illustrations_angabe' => array('034M', 'a'),
        'fortlaufendes_sammelwerk_titel' => array('036E/00', 'a'),
        'zaehlung_hauptreihe' => array('036E/00', 'l'),
        'voraus_ersch_termin' => array('mult' => true, 'values' => array(array('037D', 'a'), array('011@', 'a'))),
        'sachgruppe_quelle' => array('045G', 'A'),
        'sachgruppe_ddc' => array('045G', 'e'),
        'sachgruppe' => array('045G', 'a'),
        'preis' => array('091O/28', 'a'),
        'ppn' => array('003@', '0'),
        'gvkt_mak' => array('091O/99', 'y'),
        'gvkt_ppn' => array('091O/99', 'a'),
        'reihe' => array('091O/04', 'd'),
        'gehoert_zu_1' => array('036E', 'a'),
        'gehoert_zu_2' => array('036E/01', 'a'),
        'gehoert_zu_3' => array('036C', 'a'),
        'gehoert_zu_4' => array('036C/01', 'a')
    );

    /**
     * Creates a new title from the given JSON.
     *
     * @param $json array JSON Data
     */
    public function __construct($json) {
        $this->j = $json;

        if (is_null($json['XX02']) || !$json['XX02']) {
            $this->cover = NULL;
        } else {
            $this->cover = $json['XX02'];
        }

        if (preg_match('/^(.*?),\sIlns=(.*)/', $this->get('gvkt_mak'), $m)) {
            $this->mak = $m[1];
            $this->ilns = $m[2];
        } else {
            $this->mak = $this->get('gvkt_mak');
            $this->ilns = NULL;
        }
    }

    /**
     * Gets a property of the title
     *
     * @param $v string Desired property
     * @return mixed|null|string
     */
    public function get($v) {

        $fields = isset(self::$lookup[$v]) ? self::$lookup[$v] : NULL;
        if ($fields) { // there is an entry in the lookup array
            if (isset($fields['mult'])) { // is the entry ambiguous?
                $possible = $fields['values'];
                foreach ($possible as $p) {
                    $r = $this->getKV($p);
                    if ($r !== NULL) {
                        return $r;
                    }
                }
                return NULL;
            } else {
                return $this->getKV($fields);
            }
        } else {
            return NULL;
        }
    }

    /**
     * Gets a key-value pair.
     *
     * @param $v
     * @return mixed|null|string
     */
    private function getKV($v) {
        if (sizeof($v) == 2) {
            return isset($this->j[$v[0]][$v[1]]) ? $this->prepare($this->j[$v[0]][$v[1]]) : NULL;
        } else {
            return isset($this->j[$v[0]][0]) ? $this->prepare($this->j[$v[0]][0]) : NULL;
        }
    }

    /**
     * Gets a property directly from the json without using the lookup.
     *
     * @param $v string Property
     * @return string|array
     */
    public function getDirectly($v) {
        return isset($this->j[$v]) ? $this->j[$v] : NULL;
    }

    /**
     * Prepares the returned value
     *
     * @param $str string The value
     * @return mixed|string
     */
    private function prepare($str) {
        $str = preg_replace('/@/', '', $str, 1);
        if (preg_match('/^\d{6}$/', $str)) {
            $months = array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
            $month = intval(substr($str, 4));
            return $months[($month - 1)] . ' ' . substr($str, 0, 4);
        }
        if (preg_match('/EUR (.*?),/', $str)) {
            $p = explode(',', $str);
            $o = '';
            foreach ($p as $price) {
                $o .= $price . '<br>';
            }
            return $o;
        }
        return $str;
    }

    /**
     * Check if a key exists or if there is a value set for the key.
     *
     * @param $v string Key
     * @return bool
     */
    public function exists($v) {
        $fields = isset(self::$lookup[$v]) ? self::$lookup[$v] : NULL;
        if ($fields) {
            return isset($this->j[$fields[0]][$fields[1]]) ? true : false;
        } else {
            return false;
        }
    }

    /**
     * @return float|null The price in euros.
     */
    public function getEURPrice() {

        $pinf = $this->getDirectly('004A');
        $pr = NULL;

        if (!is_null($pinf) && isset($pinf['f'])) {
            preg_match_all('/EUR (\d+.{0,1}\d{0,2})(\s*\(DE\)){0,1}/', $pinf['f'], $m);

            if (count($m) >= 2 && count($m[1]) >= 1) {
                $pr = floatval($m[1][0]);
            }
        }

        return $pr;
    }

    /**
     * @return bool true if the title is in cart
     */
    public function isInCart() {
        return ($this->j['status'] === 'cart');
    }

    /**
     * @return bool true if the title is in a watchlist
     */
    public function isInWatchlist() {
        return !is_null($this->j['watchlist']);
    }

    /**
     * @return int|null the ID of the watchlist
     */
    public function getWlID() {
        return $this->j['watchlist'];
    }

    /**
     * @return string|null Name of the watchlist
     */
    public function getWlName() {
        return $this->wl_name;
    }

    /**
     * Sets the name of the watchlist.
     *
     * @param $name string Name
     */
    public function setWlName($name) {
        $this->wl_name = $name;
    }

    /**
     * @return string Lieferant
     */
    public function getLft() {
        return $this->j['lieft'];;
    }

    /**
     * @return string Budget
     */
    public function getBdg() {
        return $this->j['budget'];
    }

    /**
     * Getter for the complete JSON of the title
     * @return array JSON
     */
    public function export() {
        return $this->j;
    }

    /**
     * @return bool true if the title is done
     */
    public function isDone() {
        return ($this->j['status'] === 'done');
    }

    /**
     * @return bool true if the title is done
     */
    public function isPending() {
        return ($this->j['status'] === 'pending');
    }


    /**
     * @return bool true if the title has been rejected
     */
    public function isRejected() {
        return ($this->j['status'] === 'rejected');
    }

    /**
     * @return string SSG-Nr.
     */
    public function getSSGNr() {
        return $this->j['ssgnr'];
    }

    /**
     * @return string Selektionscode
     */
    public function getSelcode() {
        return $this->j['selcode'];
    }

    /**
     * @return string comment
     */
    public function getComment() {
        return $this->j['comment'];
    }

    /**
     * @return bool true if there is a cover assigned to this title
     */
    public function hasCover() {
        return !is_null($this->cover);
    }

    /**
     * @return null|string URL of the medium sized cover
     */
    public function getMediumCover() {
        if (!is_null($this->cover)) {
            return $this->cover['md'];
        } else {
            return NULL;
        }
    }

    /**
     * @return null|string URL of the large cover
     */
    public function getLargeCover() {
        if (!is_null($this->cover)) {
            return $this->cover['lg'];
        } else {
            return NULL;
        }
    }

    /**
     * Getter for every person assigned to this title.
     * @return array List of persons
     */
    public function getAssigned() {
        $refs = array();
        foreach ($this->getDirectly('XX00') as $r) {
            $k = isset($r['e']) ? $r['e'] : NULL;
            $refs[] = $k;
        }
        return $refs;
    }

    /**
     * @return null|string ILNS
     */
    public function getILNS() {
        return $this->ilns;
    }

    /**
     * @return null|string MAK
     */
    public function getMAK() {
        return $this->mak;
    }

    public function getUser(){
        return $this->j['user'];
    }

    public function getStatus(){
        return $this->j['status'];
    }
}
?>
