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
    public function download(string $youtubeLink, string $fileName): void
    {
        $this->tryDownload($youtubeLink, $fileName, 10);
    }

    /**
     * @throws Exception
     */
    private function tryDownload(string $youtubeLink, string $fileName, int $triesLeft): void
    {
        $fileUrl = $this->finder->find($youtubeLink);
        
        //The path & filename to save to.
        $saveTo = $fileName;

        $this->createFoldersIfNeeded($saveTo);
        
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
            $this->tryDownload($youtubeLink, $fileName, $triesLeft - 1);
        } elseif ($statusCode !== 200) {
            throw new Exception("Status Code: " . $statusCode);
        }
    }

    public function createFoldersIfNeeded(string $saveTo): void
    {
        $explodedPath = explode(DIRECTORY_SEPARATOR, $saveTo);

        $prevPath = '';
        for ($i = 0; $i < count($explodedPath) - 1; $i++) {
            if ($prevPath) {
                $prevPath .= DIRECTORY_SEPARATOR;
            }
            $prevPath .= $explodedPath[$i];
            if (! is_dir($prevPath)) {
                mkdir($prevPath);
            }
        }
    }
}
