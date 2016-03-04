<?php
/**
 * Changes the status of collections and views
 *
 */

/**
 * @package AJAX\Changers
 */
namespace AJAX\Changers;


use Profildienst\DB;
use Profildienst\TitleList;

/**
 * The CollectionStatusChanger is capable to change the status of a collection. A collection can either be an array of
 * title ids or a whole view. A view itself consists of all titles with a common status. This construct allows to move
 * multiple titles from one status to another (for instance to reject all titles in the main view)
 *
 * Class CollectionStatusChanger
 */
class CollectionStatusChanger{

    /**
     * A convenience method for handling both collection and views.
     * If the $view parameters is empty, the status of the titles in
     * the $id collection is changed. Otherwise the status of all titles
     * with the status $view is changed to the desired status $to.
     *
     * @param $ids array|null Array of title (can be NULL if $view is not empty)
     * @param $view string Status for view selection (can be empty if $ids is not empty)
     * @param $to Desired status
     * @param $auth Auth token
     * @throws \Exception
     */
    public static function handleCollection($ids, $view, $to, $auth){

        // check if we got all the data we need
        if ($view === '' && (is_null($ids) || !is_array($ids) || count($ids) === 0)) {
            throw new \Exception('Unvollständige Daten');
        }

        if($view !== '' && $view === 'overview'){
            $view = 'normal';
        }

        if($view === ''){
            CollectionStatusChanger::changeStatusOfCollection($ids, $to, $auth);
        }else{
            CollectionStatusChanger::changeStatusOfView($view, $to, $auth);
        }
    }

    /**
     * Changes the status of the collection to $to
     *
     * @param array $ids Collection of title ids whose status should be changed
     * @param $to Desired status
     * @param $auth Auth token
     */
    public static function changeStatusOfCollection(array $ids, $to, $auth){

        $query = array(
            '$and' => array(
                array('user' => $auth->getID()),
                array('_id' => array('$in' => $ids))
            )
        );

        $set =  array(
            '$set' => array(
                'status' => $to,
                'lastStatusChange' => new \MongoDate()
            )
        );

        DB::upd($query, $set, 'titles', array('multiple' => true));
    }

    /**
     * Changes the status of a whole view (all titles with status $from)
     *
     * @param $from Status for view selection
     * @param $to Desired status
     * @param $auth Auth token
     */
    public static function changeStatusOfView($from, $to, $auth){

        $query = array(
            '$and' => array(
                array('user' => $auth->getID()),
                array('status' => $from)
            )
        );

        $set =  array(
            '$set' => array(
                'status' => $to,
                'lastStatusChange' => new \MongoDate()
            )
        );

        DB::upd($query, $set, 'titles', array('multiple' => true));

    }


}