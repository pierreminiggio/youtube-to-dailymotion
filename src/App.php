<?php

namespace PierreMiniggio\YoutubeChannelCloner;

use Dailymotion;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionApiLogin;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionFileUploader;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionUploadUrl;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionVideoCreator;
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

            $dmVideoUploaderIfNeeded = new DailymotionVideoUploaderIfNeeded();

            $dmChannelId = null;

            if (
                isset($group['dailymotion'])
                && isset($group['dailymotion']['channelId'])
                && isset($group['dailymotion']['username'])
                && isset($group['dailymotion']['password'])
                && isset($group['dailymotion']['api'])
                && isset($group['dailymotion']['api']['key'])
                && isset($group['dailymotion']['api']['secret'])
            ) {
                $dmChannelId = $group['dailymotion']['channelId'];
                $dmUsername = $group['dailymotion']['username'];
                $dmPassword = $group['dailymotion']['password'];
                $dmApiKey = $group['dailymotion']['api']['key'];
                $dmApiSecret = $group['dailymotion']['api']['secret'];

                $dmLogin = new DailymotionApiLogin();
                $dmUploadUrlCreator = new DailymotionUploadUrl();
                $dmFileUploader = new DailymotionFileUploader($dmApiKey, $dmApiSecret, $dmUsername, $dmPassword);
                $dmAPI = new Dailymotion();
                $dmAPI->setGrantType(
                    Dailymotion::GRANT_TYPE_PASSWORD,
                    $dmApiKey,
                    $dmApiSecret,
                    [
                        'manage_videos'
                    ],
                    [
                        'username' => $dmUsername,
                        'password' => $dmPassword
                    ]
                );
                $dmVideoCreator = new DailymotionVideoCreator($dmAPI);

                $dmVideos = $dmVideoFetcher->fetch($dmChannelId);
                $dmVideosToCheck = $dmVideos;
            }

            $youtubeChannel = $group['youtube'];

            $youtubeVideos = array_reverse($lastestYoutubeVideosFetcher->fetch($youtubeChannel));

            foreach ($youtubeVideos as $youtubeVideo) {

                $this->downloadVideoIfNeeded($youtubeVideoDownloader, $youtubeVideo);
                $dmVideoUploaderIfNeeded->uploadIfNeeded(
                    $youtubeVideo,
                    $dmChannelId,
                    $dmVideosToCheck,
                    $dmLogin,
                    $dmApiKey,
                    $dmApiSecret,
                    $dmUsername,
                    $dmPassword,
                    $dmUploadUrlCreator,
                    $dmFileUploader,
                    $dmVideoCreator
                );
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
