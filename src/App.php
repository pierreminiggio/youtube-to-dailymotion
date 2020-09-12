<?php

namespace PierreMiniggio\YoutubeChannelCloner;

use PierreMiniggio\YoutubeChannelCloner\Youtube\LatestVideosFetcher;
use PierreMiniggio\YoutubeChannelCloner\Youtube\VideoFileDownloader;

class App
{
    public function run(): int
    {
        $filePath = (new VideoFileDownloader())->download(
            'https://www.youtube.com/watch?v=j4HiDzdhB8k',
            'test 1'
        );
        var_dump($filePath);
        /*$youtubeVideos = (new LatestVideosFetcher())->fetch('catoonthecat');
        var_dump($youtubeVideos);*/
        return 0;
    }
}
