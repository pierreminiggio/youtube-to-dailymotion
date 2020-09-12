<?php

namespace PierreMiniggio\YoutubeChannelCloner\Youtube;

use Exception;
use YouTube\YouTubeDownloader;

class BestDownloadLinkFinder
{

    private YouTubeDownloader $yt;

    public function __construct()
    {
        $this->yt = new YouTubeDownloader();
    }

    /**
     * @throws Exception
     */
    public function find(string $youtubeLink): string
    {
        $links = $this->yt->getDownloadLinks($youtubeLink);

        $bestFormat = 0;
        $bestLink = null;

        foreach ($links as $link) {
            if (
                strpos($link['format'], 'mp4') !== false
                && strpos($link['format'], 'audio') !== false
            ) {
                $explodedFormat = explode(',', $link['format']);
                $format = (int) substr(trim($explodedFormat[2]), 0, -1);
                if ($format > $bestFormat) {
                    $bestFormat = $format;
                    $bestLink = $link['url'];
                }
            }
        }

        if ($bestLink === null) {
            throw new Exception('Best link not found');
        }
        
        return $bestLink;
    }
}