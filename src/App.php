<?php

namespace PierreMiniggio\YoutubeChannelCloner;

use PierreMiniggio\YoutubeChannelCloner\Dailymotion\DailymotionVideoUploaderIfNeeded;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\LatestVideosFetcher as LatestDailymotionVideoFetcher;
use PierreMiniggio\YoutubeChannelCloner\Youtube\LatestVideosFetcher as LatestYoutubeVideoFetcher;
use PierreMiniggio\YoutubeChannelCloner\Youtube\VideoFileDownloader;
use PierreMiniggio\YoutubeChannelCloner\Youtube\YoutubeVideo;

class App
{
    public function run(): int
    {
        $config = require(getcwd() . DIRECTORY_SEPARATOR . 'config.php');

        $lastestYoutubeVideosFetcher = new LatestYoutubeVideoFetcher();
        $youtubeVideoDownloader = new VideoFileDownloader();

        $dmVideoFetcher = new LatestDailymotionVideoFetcher();
    
        foreach ($config['groups'] as $group) {

            $dmVideoUploaderIfNeeded = null;

            if (
                isset($group['dailymotion'])
                && isset($group['dailymotion']['channelId'])
                && isset($group['dailymotion']['username'])
                && isset($group['dailymotion']['password'])
                && isset($group['dailymotion']['api'])
                && isset($group['dailymotion']['api']['key'])
                && isset($group['dailymotion']['api']['secret'])
            ) {

                $dmVideoUploaderIfNeeded = new DailymotionVideoUploaderIfNeeded(
                    $group['dailymotion']['channelId'],
                    $group['dailymotion']['username'],
                    $group['dailymotion']['password'],
                    $group['dailymotion']['api']['key'],
                    $group['dailymotion']['api']['secret'],
                    $dmVideoFetcher
                );
            }

            $youtubeChannel = $group['youtube'];

            $youtubeVideos = array_reverse($lastestYoutubeVideosFetcher->fetch($youtubeChannel));

            foreach ($youtubeVideos as $youtubeVideo) {

                $this->downloadVideoIfNeeded($youtubeVideoDownloader, $youtubeVideo);

                if ($dmVideoUploaderIfNeeded) {
                    $dmVideoUploaderIfNeeded->uploadIfNeeded($youtubeVideo);
                }
            }
        }

        return 0;
    }

    private function downloadVideoIfNeeded(VideoFileDownloader $downloader, YoutubeVideo $video): void
    {
        $videoFilePath = $video->getSavedPath();
        if (! file_exists($videoFilePath)) {
            $downloader->download(
                $video->getUrl(),
                $videoFilePath
            );
        }
    }
}
