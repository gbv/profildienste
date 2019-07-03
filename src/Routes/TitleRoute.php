<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 13:44
 */

namespace Routes;

use Profildienst\Title\TitleRepository;
use Profildienst\User\User;
use Responses\BasicResponse;
use Responses\ActionResponse;
use Exceptions\UserErrorException;
use Interop\Container\ContainerInterface;

class TitleRoute extends Route {

    use ActionHandler;

    /**
     * @var TitleRepository
     */
    private $titleRepository;

    /**
     * @var User
     */
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
            throw new UserErrorException('This operation is currently not implemented for views or multiple titles.');
        }

        // validate values to update
        $parameters = $request->getParsedBody();
        $save = $parameters['save'];

        if (!is_array($save) || count($save) < 1) {
            throw new UserErrorException('Illegal format for save parameters');
        }

        $updatedOrderInformation = [];
        foreach ($save as $orderInfo) {
            $type = $orderInfo['type'] ?? null;
            $value = $orderInfo['value'] ?? null;

            if (is_null($type) || is_null($value) || !in_array($type, ['budget', 'supplier', 'selcode', 'ssgnr', 'comment'])) {
                throw new UserErrorException('Save parameter must have a valid type and value');
            }

            $updatedOrderInformation[$type] = $value;
        }


        if (!$this->titleRepository->changeOrderInformationOfTitles($affected, $updatedOrderInformation)) {
            throw new UserErrorException('Failed to save the updated order information');
        }


        return self::generateJSONResponse(new ActionResponse($affected, 'save', $updatedOrderInformation), $response);
    }

    public function delete($request, $response, $args) {
        if (!$this->titleRepository->deleteRejectedTitles()) {
            throw new UserErrorException('Failed to delete rejected titles.', true);
        }

        return self::generateJSONResponse(new BasicResponse([]), $response);
    }

    public function titleInfo($request, $response, $args) {

        $id = $args['id'];

        if (empty($id)) {
            throw new UserErrorException('No title ID given');
        }

        $titles = $this->titleRepository->findTitlesById([$id]);

        if (count($titles) !== 1) {
            throw new UserErrorException('No title with that ID found');
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
            throw new UserErrorException('No title ID given');
        }

        $titles = $this->titleRepository->findTitlesById([$id]);

        if (count($titles) !== 1) {
            throw new UserErrorException('No title with that ID found');
        }

        $query = $titles[0]->getTitle() . ' ' . $titles[0]->getAuthor();

        $opacUrl = $this->user->getLibrary()->getOPACURL();
        if (empty($opacUrl)) {
            throw new UserErrorException('No OPAC set for your library.');
        }

        $url = preg_replace('/%SEARCH_TERM%/', urlencode($query), $opacUrl);

        return self::generateJSONResponse(new BasicResponse(['opac' => $url]), $response);

    }

    /**
     * Returns the raw pica data for a title
     *
     * @param $request
     * @param $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws UserErrorException
     */
    public function getTitlePicaData($request, $response, $args){

        $id = $args['id'];

        if (empty($id)) {
            throw new UserErrorException('No title ID given');
        }

        $titles = $this->titleRepository->findTitlesById([$id]);

        if (count($titles) !== 1) {
            throw new UserErrorException('No title with that ID found');
        }

        $title = $titles[0];

        $titleData = $title->getPicaData();
        return self::generateJSONResponse(new BasicResponse(['pica' => $titleData]), $response);
    }
    
    public function changeTitleUser($request, $response, $args) {
        
        $id = $args['id'];
        $uid = $this->user->getId();
        $parameters = $request->getParsedBody();
        $colleague = $parameters['colleague'];

        if (!is_string($colleague) || empty($colleague)) {
            throw new UserErrorException('Illegal format for colleague parameter');
        }
        
        if(empty($id)) {
            throw new UserErrorException('No title ID given');
        }
        
        $titles = $this->titleRepository->findTitlesById([$id]);
        
        if(empty($titles)){
            throw new UserErrorException('Title not found');
        }
        
        $title = $titles[0];
        
        if(!$this->user->hasColleague($colleague)){
            throw new UserErrorException('Colleague not found');
        }
        
        $succ = $this->titleRepository->updateTitleUser($id, $colleague);
        
        if($succ) {
            return self::generateJSONResponse(new BasicResponse([]), $response);
        }else{
            throw new UserErrorException('Title could not be forwarded');
        }
        
    }

}
