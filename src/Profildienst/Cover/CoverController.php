<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 01.12.17
 * Time: 12:58
 */

namespace Profildienst\Cover;


use Config\Configuration;
use Exceptions\UserErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Rebuy\EanIsbn\Converter\Converter;
use Rebuy\EanIsbn\Parser\Ean13Parser;
use Rebuy\EanIsbn\Parser\Isbn10Parser;
use Rebuy\EanIsbn\Parser\Parser;

class CoverController {

    private $config;

    private $client;

    private $parser;

    public function __construct(Configuration $config) {
        $this->config = $config;

        $this->client = new Client([
            'base_uri' => 'https://api.vlb.de/api/v1/cover/',
            'timeout'  => 5.0,
        ]);

        $this->parser = new Parser([new Ean13Parser(), new Isbn10Parser()]);
    }

    public function getCover(string $rawISBN, string $size) {
        $isbn = null;
        try {
            $isbn = $this->parser->parse($rawISBN);

        } catch(\Exception $e) {
            throw new UserErrorException('The supplied ISBN does not seem to be valid.');
        }

        $req = null;
        try {
            $req = $this->client->request('GET', $isbn . '/' . $size, [
                'query' => [
                    'access_token' => $this->config->getCoverVLBToken()
                ]
            ]);
        } catch (\Exception $e) {
            return null;
        }

        if ($req->getStatusCode() === 200) {
            $mime = empty($req->getHeader('Content-Type'))
                ? 'image/jpeg'
                : ($req->getHeader('Content-Type'))[0];

            return [
                'mime' => $mime,
                'cover' => $req->getBody()
            ];
        } else {
            return null;
        }
    }

}
