<?php

namespace Src;

error_reporting(E_ALL);

class Manifest
{

    private $requestMethod;
    private $persistentIdentifier;

    public function __construct($requestMethod, $persistentIdentifier)
    {

        $this->requestMethod = $requestMethod;
        $this->persistentIdentifier = $persistentIdentifier;

    }

    public function processRequest()
    {

        switch ($this->requestMethod) {
            case 'GET':
                $response = self::getManifest();
                break;
            default:
                $response = self::noFoundResponse();
                break;
        }

        if (!$response) {
            return $this->noFoundResponse();
        }

        print $response;

    }

    private function getManifest()
    {

        $mods = Request::getDatastream('MODS', $this->persistentIdentifier, 'xml');
        $iiif = json_encode($mods);

        return $iiif;

    }

    private function noFoundResponse()
    {

        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;

        return $response;

    }

}

?>
