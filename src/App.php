<?php

namespace PierreMiniggio\YoutubeToDailymotion;

use PierreMiniggio\YoutubeToDailymotion\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\DailymotionVideoUploaderIfNeeded;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\LatestVideosFetcher as LatestDailymotionVideoFetcher;
use PierreMiniggio\YoutubeToDailymotion\Repository\LinkedChannelRepository;
use PierreMiniggio\YoutubeToDailymotion\Repository\NonUploadedVideoRepository;
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
            $databaseConnection = (new DatabaseConnectionFactory())->makeFromConfig($config['db']);
            $channelRepository = new LinkedChannelRepository($databaseConnection);
            $nonUploadedVideoRepository = new NonUploadedVideoRepository($databaseConnection);

            $channels = $channelRepository->findAll();
        
            foreach ($channels as $channel) {
                echo PHP_EOL . PHP_EOL . 'Checking channel ' . $channel['dailymotion_id'] . '...';

                $videosToUpload = $nonUploadedVideoRepository->findByDailymotionAndYoutubeChannelIds($channel['d_id'], $channel['y_id']);

                echo PHP_EOL . count($videosToUpload) . ' video(s) to upload :' . PHP_EOL;
                
                foreach ($videosToUpload as $videoToUpload) {
                    echo PHP_EOL . 'Uploading ' . $videoToUpload['title'] . ' ...';

                    echo PHP_EOL . $videoToUpload['title'] . ' uploaded !';
                }

                echo PHP_EOL . PHP_EOL . 'Done for channel ' . $channel['dailymotion_id'] . ' !';
            }
        }
        die('test');
    
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
