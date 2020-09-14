<?php

namespace PierreMiniggio\YoutubeChannelCloner\Dailymotion;

class LatestVideosFetcher
{

    /**
     * @return DailyMotionVideo[]
     */
    public function fetch(string $username): array
    {
        $videos = [];

        // On initialise le CURL
        $curl = curl_init();

        // On paramètre le CURL
        $url = "https://api.dailymotion.com/user/$username/videos?family_filter=false";
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        // On exécute le CURL et on récupère un résultat JSON contenant l'access token et sa durée de validité
        $jsonString = curl_exec($curl);

        // On termine le CURL
        curl_close($curl);

        if ($jsonString) {
            if ($json = json_decode($jsonString, true)) {
                if (isset($json['list']) && is_array($json['list'])) {
                    foreach ($json['list'] as $jsonVideo) {
                        $videos[] = new DailymotionVideo($jsonVideo['id'], $jsonVideo['title']);
                    }
                }
            }
        }

        return $videos;
    }
}
