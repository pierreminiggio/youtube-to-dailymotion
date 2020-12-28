<?php

namespace PierreMiniggio\YoutubeToDailymotion;

use PierreMiniggio\YoutubeToDailymotion\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\DailymotionVideoUploaderIfNeeded;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\LatestVideosFetcher as LatestDailymotionVideoFetcher;
use PierreMiniggio\YoutubeToDailymotion\Repository\LinkedChannelRepository;
use PierreMiniggio\YoutubeToDailymotion\Youtube\VideoFileDownloader;
use PierreMiniggio\YoutubeToDailymotion\Youtube\YoutubeVideo;

class App
{
    public function run(): int
    {
        $config = require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php');
        
        $youtubeVideoDownloader = new VideoFileDownloader();
        $dmVideoFetcher = new LatestDailymotionVideoFetcher();

        if (! empty($config['db'])) {
            $repository = new LinkedChannelRepository((new DatabaseConnectionFactory())->makeFromConfig($config['db']));
            $channels = $repository->findAll();
            var_dump($channels); die();
        }
    
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
