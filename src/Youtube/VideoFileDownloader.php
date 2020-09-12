<?php

namespace PierreMiniggio\YoutubeChannelCloner\Youtube;

use Exception;

class VideoFileDownloader
{

    public function download(string $youtubeLink, string $fileName): string
    {
        $fileUrl = (new BestDownloadLinkFinder())->find($youtubeLink);
        
        //The path & filename to save to.
        $saveTo = 'videos' . DIRECTORY_SEPARATOR . $fileName . '.mp4';
        
        //Open file handler.
        $fp = fopen($saveTo, 'w+');
        
        //If $fp is FALSE, something went wrong.
        if ($fp === false) {
            throw new Exception('Could not open: ' . $saveTo);
        }
        
        //Create a cURL handle.
        $ch = curl_init($fileUrl);
        
        //Pass our file handle to cURL.
        curl_setopt($ch, CURLOPT_FILE, $fp);
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        
        //Execute the request.
        curl_exec($ch);
        
        //If there was an error, throw an Exception
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        //Get the HTTP status code.
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        //Close the cURL handler.
        curl_close($ch);
        
        //Close the file handler.
        fclose($fp);
        
        if ($statusCode !== 200) {
            throw new Exception("Status Code: " . $statusCode);
        }

        return $saveTo;
    }
}