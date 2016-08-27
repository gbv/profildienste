<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 13:44
 */

namespace Routes;

use Exceptions\UserException;
use Interop\Container\ContainerInterface;
use Responses\ActionResponse;
use Responses\BasicResponse;

class TitleRoute extends Route {

    use ActionHandler;

    private $titleRepository;
    private $user;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->titleRepository = $this->ci->get('titleRepository');
        $this->user = $this->ci->get('user');
    }


    public function saveTitleInformation($request, $response, $args) {

        $affected = $this->validateAffectedTitles($request);

        // currently saving information is only supported for a single title
        if (!is_array($affected) || count($affected) > 1) {
            throw new UserException('This operation is currently not implemented for views or multiple titles.');
        }

        // validate values to update
        $parameters = $request->getParsedBody();
        $save = $parameters['save'];

        if (!is_array($save) || count($save) < 1) {
            throw new UserException('Illegal format for save parameters');
        }

        $updatedOrderInformation = [];
        foreach ($save as $orderInfo) {
            $type = $orderInfo['type'] ?? null;
            $value = $orderInfo['value'] ?? null;

            if (is_null($type) || is_null($value) || !in_array($type, ['budget', 'supplier', 'selcode', 'ssgnr', 'comment'])) {
                throw new UserException('Save parameter must have a valid type and value');
            }

            $updatedOrderInformation[$type] = $value;
        }


        if (!$this->titleRepository->changeOrderInformationOfTitles($affected, $updatedOrderInformation)) {
            throw new UserException('Failed to save the updated order information');
        }


        return self::generateJSONResponse(new ActionResponse($affected, 'save', $updatedOrderInformation), $response);
    }

    public function delete($request, $response, $args) {
///**
// * Delete titles
// */
//$app->post('/delete', $authenticate($app, $auth), function () use ($app, $auth) {
//  $m = new \AJAX\Delete($auth);
//  printResponse($m->getResponse());
//});
//
    }

    public function titleInfo($request, $response, $args) {

        $id = $args['id'];

        if (empty($id)) {
            throw new UserException('No title ID given');
        }

        $titles = $this->titleRepository->findTitlesById([$id]);

        if (count($titles) !== 1) {
            throw new UserException('No title with that ID found');
        }

        $title = $titles[0];

        $data = [];

        $url = $title->getAdditionalInfoURL();
        $mime = $title->getAdditionalInfoMimeType();

        if ($mime === 'text/html') {
            $f = file_get_contents($url);
            preg_match('/<body>(.*?)<\/body>/si', $f, $matches);
            $data['type'] = 'html';
            $data['content'] = $matches[1];
        } else {
            $data['type'] = 'other';
            $data['content'] = $url;
        }

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

    public function getOPACLink($request, $response, $args) {

        $id = $args['id'];

        if (empty($id)) {
            throw new UserException('No title ID given');
        }

        $titles = $this->titleRepository->findTitlesById([$id]);

        if (count($titles) !== 1) {
            throw new UserException('No title with that ID found');
        }

        $query = $titles[0]->getTitle() . ' ' . $titles[0]->getAuthor();

        $opacUrl = $this->user->getLibrary()->getOPACURL();
        if (empty($opacUrl)) {
            throw new UserException('No OPAC set for your library.');
        }

        $url = preg_replace('/%SEARCH_TERM%/', urlencode($query), $opacUrl);

        return self::generateJSONResponse(new BasicResponse(['opac' => $url]), $response);

    }

}