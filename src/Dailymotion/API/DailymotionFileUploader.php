<?php

namespace PierreMiniggio\YoutubeToDailymotion\Dailymotion\API;

class DailymotionFileUploader
{

    public function upload(string $uploadUrl, string $filePath): ?string
    {
        $formattedFile = function_exists('curl_file_create')
            ? curl_file_create(str_replace('\\', '/', $filePath))
            : sprintf("@%s", $filePath)
        ;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            ['file' => $formattedFile]
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        if (empty($response)) {
            return null;
        }
        
        $jsonResponse = json_decode($response, true);

        if (empty($jsonResponse) || ! isset($jsonResponse['url'])) {
            return null;
        }
        
        return $jsonResponse['url'];
    }
}
