<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 26.06.16
 * Time: 16:01
 */

namespace Profildienst\Common;


use Config\Configuration;
use Profildienst\User\User;

trait MongoOptionHelper {

    private static $sortMapping = [
        'erj' => '011@.a',
        'wvn' => '006U.0',
        'tit' => '021A.a',
        'sgr' => '045G.a',
        'dbn' => '006L.0',
        'per' => '028A.a'
    ];

    private static function sortedPageOptions(Configuration $config, User $user, $page) {
        $opt = [];

        $opt['limit'] = $config->getPagesize();
        $opt['skip'] = $config->getPagesize() * $page;

        $userSettings = $user->getSettings();


        $order = $userSettings['order'] === 'asc' ? 1 : -1;

        $options['sort'] = [
            self::$sortMapping[$userSettings['sortby']] => $order
        ];


        return $opt;
    }

}