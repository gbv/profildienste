<?php
/**
 * Title representation
 *
 * Each title is represented by an instance of this class.
 * It offers various getters for a convenient access to the
 * title data.
 *
 */

/**
 * @package Profildienst
 */
namespace Profildienst\Title;
use Interop\Container\ContainerInterface;
use Profildienst\User\User;
use Profildienst\Watchlist\Watchlist;
use Profildienst\Watchlist\WatchlistManager;

/**
 * Class which represents a title
 *
 * Class Title
 */
class Title {

    private static $allowed_status = [
      'normal', 'pending', 'done', 'cart', 'watchlist', 'rejected'
    ];

    /**
     * @var array Mapping of readable names to the PICA fields
     */
    private static $lookup = [
        'ersterfassung' => ['001A', '0'],
        'letze_aenderung_datum' => ['001B', '0'],
        'letze_aenderung_uhrzeit' => ['001B', 't'],
        'statusaenderung_datum' => ['001D', '0'],
        'gattung' => ['002@', '0'],
        'isbn10' => ['004A', '0'],
        'isbn13' => ['004A', 'A'],
        'lieferbedingungen_preis' => ['004A', 'f'],
        'kommentar_lieferbedingungen_preis' => ['004A', 'm'],
        'kommentar_isbn' => ['004A', 'c'],
        'ean' => ['004L', '0'],
        'dnb_nummer_typ' => ['006L', 'c'],
        'dnb_nummer' => ['006L', '0'],
        'verbund_id_num' => ['006L', '0'],
        'verbund_id_num_vortext' => ['006L', 'c'],
        'wv_dnb' => ['006U', '0'],
        'id_ersterfasser_vortext' => ['007G', 'c'],
        'id_ersterfasser' => ['007G', '0'],
        'addr_erg_ang_url' => ['009Q', 'a'],
        'addr_erg_ang_mime' => ['009Q', 'q'],
        'addr_erg_ang_bezw' => ['009Q', '3'],
        'sprachcode' => ['010@', 'a'],
        'erscheinungsjahr' => ['011@', 'a'],
        'code_erscheinungsland' => ['019@', 'a'],
        'titel' => ['mult' => true, 'values' => [['021A', 'a'], ['021B', 'a']]],
        'untertitel' => ['mult' => true, 'values' => [['021A', 'd'], ['021B', 'd'], ['021A', 'l'], ['021B', 'l']]],
        'verfasser' => ['mult' => true, 'values' => [['021A', 'h'], ['021B', 'h']]],
        'verfasser_vorname' => ['028A', 'd'],
        'verfasser_nachname' => ['028A', 'a'],
        'ort' => ['033A', 'p'],
        'verlag' => ['033A', 'n'],
        'umfang' => ['034D', 'a'],
        'format' => ['mult' => true, 'values' => [['033I', 'a'], ['034I', 'a']]],
        'illustrations_angabe' => ['034M', 'a'],
        'fortlaufendes_sammelwerk_titel' => ['036E/00', 'a'],
        'zaehlung_hauptreihe' => ['036E/00', 'l'],
        'voraus_ersch_termin' => ['mult' => true, 'values' => [['037D', 'a'], ['011@', 'a']]],
        'sachgruppe_quelle' => ['045G', 'A'],
        'sachgruppe_ddc' => ['045G', 'e'],
        'sachgruppe' => ['045G', 'a'],
        'preis' => ['091O/28', 'a'],
        'ppn' => ['003@', '0'],
        'gvkt_mak' => ['091O/99', 'y'],
        'gvkt_ppn' => ['091O/99', 'a'],
        'reihe' => ['091O/04', 'd'],
        'gehoert_zu_1' => ['036E', 'a'],
        'gehoert_zu_2' => ['036E/01', 'a'],
        'gehoert_zu_3' => ['036C', 'a'],
        'gehoert_zu_4' => ['036C/01', 'a'],
	'einheitssachtitel_1' => ['022A/01', 'a'],
	'einheitssachtitel_2' => ['022A', 'a'],
        'uebergeordnete_gesamtheit_1' => ['036C', 'a'],
        'uebergeordnete_gesamtheit_2' => ['036C', 'l']
    ];

    /**
     * @var array JSON Data of this title
     */
    private $j;

    /**
     * @var string URL of the cover
     */
    private $cover;

    /**
     * @var string MAK MAK of the title
     */
    private $mak;

    /**
     * @var string ILNS ILNs of the title
     */
    private $ilns;

    /**
     * @var ContainerInterface
     */
    private $ci;

    /**
     * Creates a new title from the given JSON.
     *
     * @param $json array JSON Data
     * @param ContainerInterface $ci
     */
    public function __construct($json, ContainerInterface $ci) {
        $this->j = $json;

        if (is_null($json['XX02']) || !$json['XX02']) {
            $this->cover = null;
        } else {
            $this->cover = $json['XX02'];
        }

        if (preg_match('/^(.*?),\sIlns=(.*)/', $this->get('gvkt_mak'), $m)) {
            $this->mak = $m[1];
            $this->ilns = $m[2];
        } else {
            $this->mak = $this->get('gvkt_mak');
            $this->ilns = null;
        }

        // insert a convenience field with the potential watchlist id
        if (preg_match('/watchlist\/(.*)/', $this->j['status'], $match)){
            $this->j['watchlist'] = $match[1];
            $this->j['status'] = 'watchlist';
        } else {
            $this->j['watchlist'] = null;
        }

        if ($this->j['status'] === 'overview'){
            $this->j['status'] = 'normal';
        }

        $this->ci = $ci;
    }

    /**
     * Gets a property of the title
     *
     * @param $v string Desired property
     * @return mixed|null|string
     */
    private function get($v) {

        $fields = isset(self::$lookup[$v]) ? self::$lookup[$v] : null;
        if ($fields) { // there is an entry in the lookup array
            if (isset($fields['mult'])) { // is the entry ambiguous?
                $possible = $fields['values'];
                foreach ($possible as $p) {
                    $r = $this->getKV($p);
                    if ($r !== null) {
                        return $r;
                    }
                }
                return null;
            } else {
                return $this->getKV($fields);
            }
        } else {
            return null;
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
            return isset($this->j[$v[0]][$v[1]]) ? $this->prepare($this->j[$v[0]][$v[1]]) : null;
        } else {
            return isset($this->j[$v[0]][0]) ? $this->prepare($this->j[$v[0]][0]) : null;
        }
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
        $fields = isset(self::$lookup[$v]) ? self::$lookup[$v] : null;
        if ($fields) {
            return isset($this->j[$fields[0]][$fields[1]]) ? true : false;
        } else {
            return false;
        }
    }

    /**
     * Returns the price of the title in Euro or null if it isn't possible
     * to find a price in the title data.
     *
     * @return float|null The price in euro.
     */
    public function getEURPrice() {

        $pinf = $this->getDirectly('004A');
        $pr = null;

        if (!is_null($pinf) && isset($pinf['f'])) {
            preg_match_all('/EUR (\d+.{0,1}\d{0,2})(\s*\(DE\)){0,1}/', $pinf['f'], $m);

            if (count($m) >= 2 && count($m[1]) >= 1) {
                $pr = floatval($m[1][0]);
            }
        }

        return $pr;
    }

    /**
     * Gets a property directly from the json without using the lookup.
     *
     * @param $v string Property
     * @return string|array
     */
    private function getDirectly($v) {
        return $this->j[$v] ?? null;
    }

    /**
     * Check if the title is in the users cart
     *
     * @return bool true if the title is in cart
     */
    public function isInCart() {
        return ($this->j['status'] === 'cart');
    }

    /**
     * Check if the title is in a watchlist
     *
     * @return bool true if the title is in a watchlist
     */
    public function isInWatchlist() {
        return !is_null($this->j['watchlist']);
    }

    /**
     * Returns the titles watchlist as a watchlist object or null if
     * is not associated with a watchlist.
     *
     * @return Watchlist|null Titles watchlist
     */
    public function getWatchlist() {
        return $this->isInWatchlist() ? $this->ci['watchlistManager']->getWatchlist($this->j['watchlist']) : null;
    }

    /**
     * Get the Lieferant
     *
     * @return string Lieferant
     */
    public function getSupplier() {
        return $this->getDirectly('supplier');
    }

    /**
     * Get the budget
     *
     * @return string Budget
     */
    public function getBudget() {
        return $this->getDirectly('budget');
    }

    /**
     * Returns relevant export information of this title
     *
     * @return array JSON
     */
    public function export() {
        return $this->j;
    }

    /**
     * Check if the title is marked as done
     *
     * @return bool true if the title is done
     */
    public function isDone() {
        return ($this->j['status'] === 'done');
    }

    /**
     * Check if the title is pending
     *
     * @return bool true if the title is pending
     */
    public function isPending() {
        return ($this->j['status'] === 'pending');
    }


    /**
     * Check if the title has been rejected
     *
     * @return bool true if the title has been rejected
     */
    public function isRejected() {
        return ($this->j['status'] === 'rejected');
    }
    
    public function getForwardedBy() {
        return $this->getDirectly('forwardedBy');
    }

    /**
     * Get the SSG-Nr.
     *
     * @return string SSG-Nr.
     */
    public function getSSGNr() {
        return $this->getDirectly('ssgnr');
    }

    /**
     * Get the Selektionscode
     *
     * @return string Selektionscode
     */
    public function getSelcode() {
        return $this->getDirectly('selcode');
    }


    /**
     * Getter for the comment
     *
     * @return string comment
     */
    public function getComment() {
        return $this->getDirectly('comment');
    }

    /**
     * Check if the title has a cover
     *
     * @return bool true if there is a cover assigned to this title
     */
    public function hasCover() {
        return !is_null($this->cover);
    }

    /**
     * Returns the URL of the medium sized cover or null if the title
     * does not have a cover
     *
     * @return null|string URL of the medium sized cover
     */
    public function getMediumCover() {
        return !is_null($this->cover) ? $this->cover['md'] : null;
    }

    /**
     * Returns the URL of the large cover or null if the title
     * does not have a cover
     *
     * @return null|string URL of the large cover
     */
    public function getLargeCover() {
        return !is_null($this->cover) ? $this->cover['lg'] : null;
    }

    /**
     * Getter for every person assigned to this title.
     *
     * @return array List of persons
     */
    public function getAssigned() {
        $refs = array();
        foreach ($this->getDirectly('XX00') as $r) {
            $k = isset($r['e']) ? $r['e'] : null;
            $refs[] = $k;
        }
        return $refs;
    }

    /**
     * Getter for the ILNs
     *
     * @return null|string ILNs
     */
    public function getILNS() {
        return $this->ilns;
    }

    /**
     * Getter for the MAK
     *
     * @return null|string MAK
     */
    public function getMAK() {
        return $this->mak;
    }

    /**
     * Returns the ID of the user owning this title
     *
     * @return mixed Owners user ID
     */
    public function getUser() {
        return $this->ci['user'];
    }

    /**
     * Returns the current status of the title
     *
     * @return string The titles status
     */
    public function getStatus() {
        return $this->j['status'];
    }

    public function getAdditionalInfoMimeType(){
        return $this->get('addr_erg_ang_mime');
    }

    public function getAdditionalInfoURL(){
        return $this->get('addr_erg_ang_url');
    }

    public function getTitle(){
        return $this->get('titel');
    }

    public function getUebergeordneteGesamtheit(){

      // Merge the different "uebergeordnete_gesamtheit" fields into one
      if(!empty($this->get('uebergeordnete_gesamtheit_2'))){
        $uebergeordnete_gesamtheit_2_brackets = '('.$this->get('uebergeordnete_gesamtheit_2').')';
      }else{
        $uebergeordnete_gesamtheit_2_brackets = '';
      }

      return trim(
          join('', [
              $this->get('uebergeordnete_gesamtheit_1'),
              ' ',
              $uebergeordnete_gesamtheit_2_brackets
          ])
      );

    }

    public function getAuthor(){
        return $this->get('verfasser');
    }


    public function getId() {
        return $this->getDirectly('_id');
    }

    public function getGVKInfo(){
        return $this->get('gvkt_mak');
    }

    public function getReihe(){
        return $this->get('reihe');
    }

    public function getPPN() {
        return $this->get('ppn');
    }

    /**
     * Extracts the relevant information from a title to display it.
     *
     * @return array Array containing relevant information
     */
    public function toJson() {

        $watchlist = $this->getWatchlist();
        $wlName = is_null($watchlist) ? null : $watchlist->getName();
        $wlId= is_null($watchlist) ? null : $watchlist->getId();

        $r = [
            'id' => $this->getId(),

            'hasCover' => $this->hasCover(),
            'cover_md' => $this->getMediumCover(),
            'cover_lg' => $this->getLargeCover(),

            'titel' => $this->get('titel'),
	    'einheitssachtitel_1' => $this->get('einheitssachtitel_1'),
	    'einheitssachtitel_2' => $this->get('einheitssachtitel_2'),
            'untertitel' => $this->get('untertitel'),
            'verfasser' => $this->get('verfasser'),
            'ersch_termin' => $this->get('voraus_ersch_termin'),
            'verlag' => $this->get('verlag'),
            'ort' => $this->get('ort'),
            'umfang' => $this->get('umfang'),
            'ill_angabe' => $this->get('illustrations_angabe'),
            'format' => $this->get('format'),

            'preis' => $this->get('lieferbedingungen_preis'),
            'preis_kom' => $this->get('kommentar_lieferbedingungen_preis'),

            'mak' => $this->getMAK(),
            'ilns' => $this->getILNS(),
            'ppn' => $this->get('gvkt_ppn'),

            'ersch_jahr' => $this->get('erscheinungsjahr'),
            'gattung' => $this->get('gattung'),
            'dnbnum' => $this->get('dnb_nummer'),
            'wvdnb' => $this->get('wv_dnb'),
            'sachgruppe' => $this->get('sachgruppe'),
            'zugeordnet' => $this->getAssigned(),

            'addInfURL' => $this->get('addr_erg_ang_url'),

            'supplier' => $this->getUser()->getSupplier($this->getSupplier())['name'],
	    'supplierValue' => $this->getSupplier(),
            'budget' => $this->getUser()->getBudget($this->getBudget())['name'],
	    'budgetValue' => $this->getBudget(),
            'selcode' => $this->getSelcode(),
            'ssgnr' => $this->getSSGNr(),
            'comment' => $this->getComment(),
            
            'forwardedBy' => $this->getForwardedBy(),

            'status' => [
                'rejected' => $this->isRejected(),
                'done' => $this->isDone(),
                'cart' => $this->isInCart(),
                'pending' => $this->isPending(),
                'lastChange' => ($this->isPending() || $this->isDone()) ? (int)((string)$this->getDirectly('lastStatusChange')) : '', // only show last status change for pending and done titles
                'watchlist' => [
                    'watched' => $this->isInWatchlist(),
                    'id' => $wlId,
                    'name' => $wlName
                ]
            ]
        ];

        if (!$this->hasCover()) {
            $r['cover_md'] = '';
        }

        if ($this->get('isbn13') !== null) {
            $isbn = $this->get('isbn13');
        } else {
            $isbn = $this->get('isbn10');
        }
        $r['isbn'] = $isbn;

        //Merge the different "Gehoert zu" fields into one
        $r['gehoert_zu'] = trim(
            implode(', ', array_filter([
                $this->get('gehoert_zu_1'),
                $this->get('gehoert_zu_2'),
                $this->get('gehoert_zu_3'),
                $this->get('gehoert_zu_4')
            ]))
        );

        $r['uebergeordnete_gesamtheit'] = $this->getUebergeordneteGesamtheit();

        // Create DNB link
        $r['dnb_link'] = null;
        if ($this->get('dnb_nummer_typ') === 'DNB' && !is_null($this->get('dnb_nummer'))) {
            $r['dnb_link'] = 'http://d-nb.info/' . $this->get('dnb_nummer');
        }

        return $r;
    }

    public function persist() {
        return $this->j;
    }

    /**
     * Returns the titles PICA data
     *
     * @return array Pica data
     */
    public function getPicaData(){
        $picaData = [];

        foreach ($this->j as $cat => $catData) {
            // TODO: Is the assumption correct that every PICA entry starts with 0?
            if ($cat[0] === '0') {
                $cats = [];
                foreach ($catData as $subCat => $value) {
                    $cats[] = [
                        'subfield' =>$subCat,
                        'value' => $value
                    ];
                }
                $picaData[$cat] = $cats;
            }
        }

        return $picaData;
    }

}
