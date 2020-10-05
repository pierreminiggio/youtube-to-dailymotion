<?php

namespace PierreMiniggio\YoutubeChannelCloner\Dailymotion\API;

class DailymotionVideoCreator
{
    public function create(string $accessToken, string $videoUrl, string $videoTitle): ?string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.dailymotion.com/me/videos');
        $authorization = 'Authorization: Bearer ' . $accessToken;
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            $authorization
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            http_build_query([
                'url' => $videoUrl,
                'tags' => 'developpement,informatique,découverte,éducation',
                'title' => $videoTitle,
                'channel' => 'education',
                'published' => true
            ])
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        if (empty($response)) {
            return null;
        }
        
        $jsonResponse = json_decode($response, true);
        var_dump($jsonResponse);
        
        if (empty($jsonResponse) || ! isset($jsonResponse['id'])) {
            return null;
        }
        
        return $jsonResponse['id'];
    }
}
