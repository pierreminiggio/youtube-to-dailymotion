<?php

namespace PierreMiniggio\YoutubeChannelCloner;

use PierreMiniggio\YoutubeChannelCloner\Youtube\LatestVideosFetcher;
use PierreMiniggio\YoutubeChannelCloner\Youtube\VideoFileDownloader;

class App
{
    public function run(): int
    {
        $channel = 'catoonthecat';

        $youtubeVideos = (new LatestVideosFetcher())->fetch($channel);

        $downloader = new VideoFileDownloader();

        foreach ($youtubeVideos as $youtubeVideo) {
            $videoFilePath = $youtubeVideo->getSavedPath();
            if (! file_exists($videoFilePath)) {
                $downloader->download(
                    $youtubeVideo->getUrl(),
                    $videoFilePath
                );
            }
        }

        return 0;
    }
}
