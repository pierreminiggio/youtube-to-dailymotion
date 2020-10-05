<?php

namespace PierreMiniggio\YoutubeChannelCloner\Dailymotion\API;

class DailymotionUploadUrl
{
    public function create(string $accessToken): ?string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.dailymotion.com/file/upload');
        $authorization = 'Authorization: Bearer ' . $accessToken;
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            $authorization
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        if (empty($response)) {
            return null;
        }
        
        $jsonResponse = json_decode($response, true);
        
        if (empty($jsonResponse) || ! isset($jsonResponse['upload_url'])) {
            return null;
        }
        
        return $jsonResponse['upload_url'];
    }
}
