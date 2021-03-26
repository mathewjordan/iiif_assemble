<?php

namespace Src;

class IIIF {

    private $pid;
    private $xpath;
    private $model;
    private $url;

    public function __construct($pid, $mods, $model)
    {

        $this->pid = $pid;
        $this->xpath = new XPath($mods->asXml());
        $this->model = $model;

        $this->url = Utility::getBaseUrl();

    }

    public function buildPresentation ()
    {

        $manifest['@context'] = 'https://iiif.io/api/presentation/3/context.json';
        $manifest['id'] = $this->url . $_SERVER["REQUEST_URI"];
        $manifest['type'] = 'Manifest';
        $manifest['label'] = self::getLanguageArray($this->xpath->query('titleInfo[not(@*)]'));
        $manifest['summary'] = self::getLanguageArray($this->xpath->query('abstract'));
        $manifest['metadata'] = self::buildMetadata();
        $manifest['rights'] = self::buildRights();
        $manifest['provider'] = self::buildProvider();
        $manifest['thumbnail'] = self::buildThumbnail('TN', array(200, 200));

        return json_encode($manifest);

    }

    public function buildMetadata () {

        $alternativeTitle = self::getLabelValuePair(
            'Alternative Title',
            $this->xpath->query('titleInfo[@type="alternative"]')
        );

        $identifier = self::getLabelValuePair(
            'Publication Identifier',
            $this->xpath->query('identifier')
        );

        $tableOfContents = self::getLabelValuePair(
            'Table of Contents',
            $this->xpath->query('tableOfContents')
        );

        $date = self::getLabelValuePair(
            'Date',
            $this->xpath->query('dateCreated')
        );

        return (object) [
            $alternativeTitle,
            $identifier,
            $tableOfContents,
            $date
        ];

    }

    public function buildRights () {

        $accessCondition = $this->xpath->queryElement('accessCondition');

        foreach ($accessCondition as $node) :
            foreach ($node->attributes as $attribute) :
                if ($attribute->nodeName === 'xlink:href')
                    $rights = $attribute->nodeValue;
            endforeach;
        endforeach;

        if (isset($rights)) :
            return $rights;
        else :
            return null;
        endif;

    }

    public function buildProvider () {

        return null;

    }

    public function buildThumbnail ($dsid, $size) {

        $uri = $this->url . '/iiif/2/';
        $uri .= 'collections~islandora~object~' . $this->pid;
        $uri .= '~datastream~' . $dsid;
        $uri .= '~view/full/!' . $size[0] . ',' . $size[1];
        $uri .= '/1/default.jpg';

        $format = "image/jpeg";

        return [
            (object) [
                'id' => $uri,
                'type' => "Image",
                'format' => $format,
                'width' => $size[0],
                'height' => $size[1]
            ]
        ];

    }

    public function getLabelValuePair ($label, $value) {

        return (object) [
            'label' => self::getLanguageArray($label),
            'value' => self::getLanguageArray($value)
        ];

    }

    public function getLanguageArray ($string, $language = 'en') {

        return (object) [
            $language => $string
        ];

    }

}

?>
