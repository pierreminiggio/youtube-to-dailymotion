<?php

namespace PierreMiniggio\YoutubeChannelCloner\Youtube;

use Exception;

class VideoFileDownloader
{

    private BestDownloadLinkFinder $finder;

    public function __construct()
    {
        $this->finder = new BestDownloadLinkFinder();
    }

    /**
     * @throws Exception
     */
    public function download(string $youtubeLink, string $fileName): string
    {
        return $this->tryDownload($youtubeLink, $fileName, 10);
    }

    /**
     * @throws Exception
     */
    private function tryDownload(string $youtubeLink, string $fileName, int $triesLeft): string
    {
        $fileUrl = $this->finder->find($youtubeLink);
        
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

        if ($statusCode === 302 && $triesLeft) {
            return $this->tryDownload($youtubeLink, $fileName, $triesLeft - 1);
        } elseif ($statusCode !== 200) {
            throw new Exception("Status Code: " . $statusCode);
        }

        return getcwd() . DIRECTORY_SEPARATOR . $saveTo;
    }
}
