<?php

namespace PierreMiniggio\YoutubeToDailymotion\Dailymotion\API;

class DailymotionApiLogin
{
    public function login(string $clientId, string $clientSecret, string $username, string $password): ?string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.dailymotion.com/oauth/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            http_build_query([
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'username' => $username,
                'password' => $password,
                'scope' => 'manage_videos'
            ])
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        if (empty($response)) {
            return null;
        }
        
        $jsonResponse = json_decode($response, true);
        
        if (empty($jsonResponse) || ! isset($jsonResponse['access_token'])) {
            return null;
        }
        
        return $jsonResponse['access_token'];
    }
}
