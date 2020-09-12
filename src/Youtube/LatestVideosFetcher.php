<?php

namespace PierreMiniggio\YoutubeChannelCloner\Youtube;

use SimpleXMLElement;

class LatestVideosFetcher
{

    /**
     * @return YoutubeVideo[]
     */
    public function fetch(string $username): array
    {
        $videos = [];

        // On initialise le CURL
        $curl = curl_init();

        // On paramètre le CURL
        $url = 'https://www.youtube.com/feeds/videos.xml?user=' . $username;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        // On exécute le CURL et on récupère un résultat JSON contenant l'access token et sa durée de validité
        $string = curl_exec($curl);

        // On termine le CURL
        curl_close($curl);

        if ($string) {
            $videosXML = new SimpleXMLElement($string);
            
            foreach ($videosXML->getNamespaces(true) as $alias => $namespace) {
                $videosXML->registerXPathNamespace($alias, $namespace);
            }
            foreach ($videosXML->entry as $videoXML) {
                $id = substr($videoXML->id, 9);
                $url = "https://www.youtube.com/watch?v=" . $id;
                foreach ($videoXML->xpath('media:group') as $mediaGroup) {
                    foreach ($mediaGroup->xpath('media:thumbnail') as $attribute) {
                        $thumbnail = $attribute->attributes()->url->__toString();
                    }
                    $title = $mediaGroup->xpath('media:title')[0]->__toString();
                    $description = $mediaGroup->xpath('media:description')[0]->__toString();
                }

                $videos[] = new YoutubeVideo($id, $url, $thumbnail, $title, $description);
            }
        }

        return $videos;
    }
}
