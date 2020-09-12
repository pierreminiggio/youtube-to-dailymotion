<?php

namespace PierreMiniggio\YoutubeChannelCloner;

use PierreMiniggio\YoutubeChannelCloner\Youtube\LatestVideosFetcher;
use PierreMiniggio\YoutubeChannelCloner\Youtube\VideoFileDownloader;

class App
{
    public function run(): int
    {
        $config = require(getcwd() . DIRECTORY_SEPARATOR . 'config.php');

        $lastestYoutubeVideosFetcher = new LatestVideosFetcher();
        $youtubeVideoDownloader = new VideoFileDownloader();
    
        foreach ($config['groups'] as $group) {
            $youtubeChannel = $group['youtube'];

            $youtubeVideos = $lastestYoutubeVideosFetcher->fetch($youtubeChannel);

            foreach ($youtubeVideos as $youtubeVideo) {
                $videoFilePath = $youtubeVideo->getSavedPath();
                if (! file_exists($videoFilePath)) {
                    $youtubeVideoDownloader->download(
                        $youtubeVideo->getUrl(),
                        $videoFilePath
                    );
                }
            }
        }

        return 0;
    }
}
