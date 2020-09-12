<?php

namespace PierreMiniggio\YoutubeChannelCloner;

use PierreMiniggio\YoutubeChannelCloner\Youtube\LatestVideosFetcher;

class App
{
    public function run(): int
    {
        $youtubeVideos = (new LatestVideosFetcher())->fetch('catoonthecat');
        var_dump($youtubeVideos);
        return 0;
    }
}
