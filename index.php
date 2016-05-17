<?php

use Auth\Auth;
use Config\Configuration;
use Exceptions\AuthException;
use Exceptions\BaseException;
use Firebase\JWT\JWT;
use Middleware\AuthMiddleware;
use Middleware\JSONPMiddleware;
use Profildienst\DB;
use Profildienst\User;
use Responses\APIResponse;
use Responses\BasicResponse;
use Responses\ErrorResponse;
use Slim\Http\Response;

require 'vendor/autoload.php';

set_error_handler(function ($errno, $errstr) {
  throw new Exception($errstr, $errno);
}, E_ALL);


$slimConfiguration = [
  'settings' => [
    'displayErrorDetails' => true,
  ],
];

$config = Configuration::getInstance();
$auth = new AuthMiddleware($config);

$c = new \Slim\Container($slimConfiguration);
$app = new \Slim\App($c);
$app->add(new JSONPMiddleware());

$c['errorHandler'] = function ($c) {
  return function ($request, $response, $exception) use ($c) {

    if ($exception instanceof BaseException) {

      $errResp = new ErrorResponse($exception->getModule() . ' error: ' . $exception->getMessage());
      return JSONPMiddleware::handleJSONPResponse($request, generateJSONResponse($errResp, $response));

    } else {

      // mail

      $errResp = new ErrorResponse('An internal error occured:' . $exception->getMessage());
      return JSONPMiddleware::handleJSONPResponse($request, generateJSONResponse($errResp, $response));
    }

  };
};

function generateJSONResponse(APIResponse $response, Response $out) {

  $resp = [];

  $status = $response->getHTTPReturnCode();
  if ($response instanceof ErrorResponse) {
    $resp['error'] = $response->getData();
  } else {
    $resp['data'] = $response->getData();
  }

  return $out->withJson($resp, $status);

}

$app->post('/auth', function ($request, $response, $args) use ($config) {

  $credentials = $request->getParsedBody();

  // have we got a username and a password?
  if (empty($credentials['user']) || empty($credentials['pass'])) {
    throw new AuthException('Bitte geben Sie einen Benutzername und ein Passwort ein.');
  }

  // Perform authentication. If the authentication fails, an exception will be thrown and
  // therefore the rest of this function will not be executed.
  $auth = new Auth($credentials['user'], $credentials['pass'], $config);

  // construct token
  $token = [
    'iss' => $config->getTokenIssuer(),
    'aud' => $auth->getName(),
    'sub' => $auth->getName(),
    'pd_id' => $credentials['user'],
    'iat' => time(),
    'exp' => time() + $config->getTokenExpTime()
  ];

  $jwt = JWT::encode($token, $config->getSecretKey(), $config->getTokenCryptAlgorithm());
  generateJSONResponse(new BasicResponse($jwt), $response);
});

$app->get('/libraries', function ($request, $response, $args) use ($config) {

  $data = [];
  foreach ($config->getLibraries() as $library) {
    $data[] = [
      'isil' => $library->getISIL(),
      'name' => $library->getName()
    ];
  }

  return generateJSONResponse(new BasicResponse($data), $response);
});

//
//
///**
// * Save additional informations for titles
// */
//$app->post('/save', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $id = $app->request()->post('id');
//  $type = $app->request()->post('type');
//  $content = $app->request()->post('content');
//
//  $m = new \AJAX\Save($id, $type, $content, $auth);
//  printResponse($m->getResponse());
//
//});
//
//
///**
// * Watchlists
// */
//$app->group('/watchlist', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $app->post('/remove', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $wl = $app->request()->post('wl');
//
//    $m = new \AJAX\RemoveWatchlist($id, $wl, $auth);
//    printResponse($m->getResponse());
//  });
//
//  $app->post('/add', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $wl = $app->request()->post('wl');
//
//    $m = new \AJAX\Watchlist($id, $wl, $auth);
//    printResponse($m->getResponse());
//  });
//
//  $app->post('/manage', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $type = $app->request()->post('type');
//    $content = $app->request()->post('content');
//
//    $m = new \AJAX\WatchlistManager($id, $type, $content, $auth);
//    printResponse($m->getResponse());
//  });
//
//});
//
///**
// * Cart
// */
//$app->group('/cart', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $app->post('/remove', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\RemoveCart($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//
//  $app->post('/add', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\Cart($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//});
//
///**
// * Reject
// */
//$app->group('/reject', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $app->post('/remove', function () use ($app, $auth) {
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\RemoveReject($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//
//  $app->post('/add', function () use ($app, $auth) {
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\Reject($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//});
//
/**
 * Search configuration (available fields and modes for searching)
 */
$app->get('/search', function ($request, $response, $args) use ($config) {

  $searchable_fields = [];
  foreach ($config->getSearchableFields() as $val => $name){
    $searchable_fields[] = array(
      'name' => $name,
      'value' => $val
    );
  }

  $search_modes = [];
  foreach ($config->getSearchModes() as $val => $name){
    $search_modes[] = array(
      'name' => $name,
      'value' => $val
    );
  }

  $data = [
      'searchable_fields' => $searchable_fields,
      'search_modes' => $search_modes
  ];

  return generateJSONResponse(new BasicResponse($data), $response);
})->add($auth);

/**
 * User related information
 */
$app->group('/user', function () use ($config) {

  $user = User::getInstance();

  $this->get('[/]', function ($request, $response, $args) use ($config, $user){

    // TODO: Change when database rework is finished
    $d = DB::get(array('_id' => $user->getID()), 'users', array(), true);

    $budgets = [];
    foreach ($d['budgets'] as $budget) {
      $budgets[] = [
        'key' => $budget['0'],
        'value' => $budget['c']
      ];
    }

    $data = [
      'name' => $user->getName(),
      'motd' => $config->getMOTD(),
      'defaults' => [
        'lft' => $d['defaults']['lieft'],
        'budget' => $d['defaults']['budget'],
        'ssgnr' => $d['defaults']['ssgnr'],
        'selcode' => $d['defaults']['selcode']
      ],
      'budgets' => $budgets
    ];

    return generateJSONResponse(new BasicResponse($data), $response);
  });

  $this->get('/watchlists', function ($request, $response, $args) use ($user) {

    // TODO
    $d = DB::get(array('_id' => $user->getID()), 'users', array(), true);
    $watchlists = $d['watchlist'];
    // TODO
    $wl_order = DB::getUserData('wl_order');

    $wl = [];
    foreach ($wl_order as $index) {
      $wl[] = [
        'id' => $watchlists[$index]['id'],
        'name' => $watchlists[$index]['name'],
        'count' => DB::getWatchlistSize($watchlists[$index]['id'])
      ];
    }

    $data = [
      'watchlists' => $wl,
      'def_wl' => $d['wl_default']
    ];

    return generateJSONResponse(new BasicResponse($data), $response);
  });

  $this->get('/cart', function ($request, $response, $args) use ($user) {
    // TODO
    $d = DB::get(array('_id' => $user->getID()), 'users', array(), true);

    $data = [
      'cart' => DB::getCartSize() /* TODO */,
      'price' => $d['price'],
    ];

    return generateJSONResponse(new BasicResponse($data), $response);
  });

  $this->get('/settings', function ($request, $response, $args) {

    $data = [
      'settings' => DB::getUserData('settings')
    ];

    return generateJSONResponse(new BasicResponse($data), $response);
  });

  /*
  $this->get('/orderlist', function () use ($app, $auth) {
    try {
      $m = new \Special\Orderlist($auth);

      printResponse(array('data' => array('orderlist' => $m->getOrderlist())));
    } catch (\Exception $e) {
      printResponse(NULL, true, $e->getMessage());
    }

  });*/

})->add($auth);

/**
 * Settings
 */
$app->get('/settings', function ($request, $response, $args) use ($config) {

  $sortby = array();
  foreach ($config->getSortOptions() as $val => $desc) {
    $sortby[] = array('key' => $val, 'value' => $desc);
  }

  $order = array();
  foreach ($config->getOrderOptions() as $val => $desc) {
    $order[] = array('key' => $val, 'value' => $desc);
  }

  $data = [
    'sortby' => $sortby,
    'order' => $order
  ];

  generateJSONResponse(new BasicResponse($data), $response);

})->add($auth);
//
///**
// * Delete titles
// */
//$app->post('/delete', $authenticate($app, $auth), function () use ($app, $auth) {
//  $m = new \AJAX\Delete($auth);
//  printResponse($m->getResponse());
//});
//
///**
// * Order
// */
//$app->post('/order', $authenticate($app, $auth), function () use ($app, $auth) {
//  $m = new \Special\Order($auth);
//  printResponse($m->getResponse());
//});
//
///**
// * Verlagsmeldung
// */
//$app->post('/info', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $id = $app->request()->post('id');
//
//  $m = new \AJAX\Info($id, $auth);
//  printResponse($m->getResponse());
//});
//
///**
// * OPAC Abfrage
// */
//$app->post('/opac', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $titel = $app->request()->post('titel');
//  $verfasser = $app->request()->post('verfasser');
//
//  $query = $titel . ' ' . $verfasser;
//
//  $isil = \Profildienst\DB::getUserData('isil', $auth);
//
//  $opac_url = Config::$bibliotheken[$isil]['opac'];
//
//  $url = preg_replace('/%SEARCH_TERM%/', urlencode($query), $opac_url);
//
//  printResponse(array('data' => array('url' => $url)));
//
//});
//
///**
// * Settings
// */
//$app->post('/settings', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $type = $app->request()->post('type');
//  $value = $app->request()->post('value');
//
//  $m = new \AJAX\ChangeSetting($type, $value, $auth);
//  printResponse($m->getResponse());
//});
//
//
///**
// * Loader
// */
//$app->group('/get', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $app->get('/overview/page/:num', function ($num = 0) use ($app, $auth) {
//    $m = new \Content\Main(validateNum($num), $auth);
//    printTitles($m->getTitles(), $m->getTotalCount());
//  });
//
//  $app->get('/cart/page/:num', function ($num = 0) use ($app, $auth) {
//    $m = new \Content\Cart(validateNum($num), $auth);
//    printTitles($m->getTitles(), $m->getTotalCount());
//  });
//
//  $app->get('/watchlist/:id/page/:num', function ($id = NULL, $num = 0) use ($app, $auth) {
//    $m = new \Content\Watchlist(validateNum($num), $id, $auth);
//    if (is_null($m->getTotalCount())) {
//      printResponse(NULL, true, 'Es existiert keine Merkliste mit dieser ID.');
//    } else {
//      printTitles($m->getTitles(), $m->getTotalCount());
//    }
//  });
//
//  $app->get('/search/:query/:queryType/page/:num', function ($query, $queryType = 'keyword', $num = 0) use ($app, $auth) {
//    try {
//
//      if($queryType === 'advanced'){
//        $query = json_decode($query, true);
//      }
//
//      $m = new \Search\Search($query, $queryType, $num, $auth);
//      printTitles($m->getTitles(), $m->getTotalCount(), $m->getSearchInformation());
//    } catch (\Exception $e) {
//      printResponse(NULL, true, $e->getMessage());
//    }
//
//  });
//
//
//  $app->get('/pending/page/:num', function ($num = 0) use ($app, $auth) {
//    $m = new \Content\Pending(validateNum($num), $auth);
//    printTitles($m->getTitles(), $m->getTotalCount());
//  });
//
//  $app->get('/done/page/:num', function ($num = 0) use ($app, $auth) {
//    $m = new \Content\Done(validateNum($num), $auth);
//    printTitles($m->getTitles(), $m->getTotalCount());
//  });
//
//  $app->get('/rejected/page/:num', function ($num = 0) use ($app, $auth) {
//    $m = new \Content\Rejected(validateNum($num), $auth);
//    printTitles($m->getTitles(), $m->getTotalCount());
//  });
//});
//
//$app->notFound(function () use ($app) {
//  $app->halt(404);
//});
//
///**
// * Validates a number, i.e. if the number is natural.
// *
// * @param $num string potential number
// * @return int value of the number if the number is natural or 0 otherwise
// */
//function validateNum($num) {
//  if ($num != '' && $num > 0 && is_numeric($num)) {
//    return $num;
//  } else {
//    return 0;
//  }
//}
//
///**
// * Extracts the relevant information from a title to display it.
// *
// * @param Title $t Title
// * @return array Array containing relevant information
// */
//function convertTitle(Title $t) {
//
//  $r = array(
//    'id' => $t->getDirectly('_id'),
//
//    'hasCover' => $t->hasCover(),
//    'cover_md' => $t->getMediumCover(),
//    'cover_lg' => $t->getLargeCover(),
//
//    'titel' => $t->get('titel'),
//    'untertitel' => $t->get('untertitel'),
//    'verfasser' => $t->get('verfasser'),
//    'ersch_termin' => $t->get('voraus_ersch_termin'),
//    'verlag' => $t->get('verlag'),
//    'ort' => $t->get('ort'),
//    'umfang' => $t->get('umfang'),
//    'ill_angabe' => $t->get('illustrations_angabe'),
//    'format' => $t->get('format'),
//
//    'preis' => $t->get('lieferbedingungen_preis'),
//    'preis_kom' => $t->get('kommentar_lieferbedingungen_preis'),
//
//    'mak' => $t->getMAK(),
//    'ilns' => $t->getILNS(),
//    'ppn' => $t->get('gvkt_ppn'),
//
//    'ersch_jahr' => $t->get('erscheinungsjahr'),
//    'gattung' => $t->get('gattung'),
//    'dnbnum' => $t->get('dnb_nummer'),
//    'wvdnb' => $t->get('wv_dnb'),
//    'sachgruppe' => $t->get('sachgruppe'),
//    'zugeordnet' => $t->getAssigned(),
//
//    'addInfURL' => $t->get('addr_erg_ang_url'),
//
//    'lft' => $t->getLft(),
//    'budget' => $t->getBdg(),
//    'selcode' => $t->getSelcode(),
//    'ssgnr' => $t->getSSGNr(),
//    'comment' => $t->getComment(),
//
//    'status' => array(
//      'rejected' => $t->isRejected(),
//      'done' => $t->isDone(),
//      'cart' => $t->isInCart(),
//      'pending' => $t->isPending(),
//      'lastChange' => ($t->isPending() || $t->isDone()) ? (int) ((string) $t->getDirectly('lastStatusChange')) : '', // only show last status change for pending and done titles
//      'selected' => false,
//      'watchlist' => array('watched' => $t->isInWatchlist(), 'id' => $t->getWlID(), 'name' => $t->getWlName())
//    )
//  );
//
//  if (!$t->hasCover()) {
//    $r['cover_md'] = '';
//  }
//
//  if ($t->get('isbn13') !== NULL) {
//    $isbn = $t->get('isbn13');
//  } else {
//    $isbn = $t->get('isbn10');
//  }
//  $r['isbn'] = $isbn;
//
//  //Merge the different "Gehoert zu" fields into one
//  $r['gehoert_zu'] = trim(join('', array($t->get('gehoert_zu_1'), $t->get('gehoert_zu_2'), $t->get('gehoert_zu_3'), $t->get('gehoert_zu_4'))));
//
//  // Craft DNB Link
//
//  if (!is_null($t->get('dnb_nummer'))) {
//    $r['dnb_link'] = 'http://d-nb.info/' . $t->get('dnb_nummer');
//  }
//  return $r;
//}
//
///**
// * Prints a response consisting of titles.
// *
// * @param $titles TitleList|null
// * @param $total int total amount of titles
// */
//function printTitles($titles, $total, $additionalInformation = null) {
//  $titles_out = array();
//  if (!is_null($titles)) {
//    foreach ($titles->getTitles() as $t) {
//      $titles_out[] = convertTitle($t);
//    }
//  }
//
//  if(is_null($additionalInformation)){
//    printResponse(array('more' => ($titles !== NULL), 'total' => $total, 'data' => $titles_out));
//  }else{
//    printResponse(array('more' => ($titles !== NULL), 'total' => $total, 'data' => $titles_out, 'additional' => $additionalInformation));
//  }
//
//}
//

$app->run();

