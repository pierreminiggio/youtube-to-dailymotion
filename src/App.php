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
            $downloader->download(
                $youtubeVideo->getUrl(),
                $youtubeVideo->getSavedPath()
            );
        }

        return 0;
    }
}
