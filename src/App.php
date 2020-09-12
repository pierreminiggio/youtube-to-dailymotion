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
            $filePath = $downloader->download(
                $youtubeVideo->getUrl(),
                'videos' . DIRECTORY_SEPARATOR . $channel . DIRECTORY_SEPARATOR . $youtubeVideo->getTitle()
            );
            var_dump($filePath);
        }

        return 0;
    }
}
