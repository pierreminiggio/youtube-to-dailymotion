<?php

namespace PierreMiniggio\YoutubeChannelCloner;

use PierreMiniggio\YoutubeChannelCloner\Dailymotion\DailymotionApiLogin;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\LatestVideosFetcher as LatestDailymotionVideoFetcher;
use PierreMiniggio\YoutubeChannelCloner\Youtube\LatestVideosFetcher as LatestYoutubeVideoFetcher;
use PierreMiniggio\YoutubeChannelCloner\Youtube\VideoFileDownloader;

class App
{
    public function run(): int
    {
        $config = require(getcwd() . DIRECTORY_SEPARATOR . 'config.php');

        $lastestYoutubeVideosFetcher = new LatestYoutubeVideoFetcher();
        $youtubeVideoDownloader = new VideoFileDownloader();

        $dmVideoFetcher = new LatestDailymotionVideoFetcher();
    
        foreach ($config['groups'] as $group) {
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

                $dmVideos = $dmVideoFetcher->fetch($dmChannelId);
                $dmVideosToCheck = $dmVideos;
            }

            $youtubeChannel = $group['youtube'];

            $youtubeVideos = $lastestYoutubeVideosFetcher->fetch($youtubeChannel);

            foreach ($youtubeVideos as $youtubeVideo) {
                // Download if not stored
                $videoFilePath = $youtubeVideo->getSavedPath();
                if (! file_exists($videoFilePath)) {
                    $youtubeVideoDownloader->download(
                        $youtubeVideo->getUrl(),
                        $videoFilePath
                    );
                }

                // Upload to DailyMotion if not uploaded
                if ($dmChannelId) {
                    // Check if on DM
                    $isVideoUploadedOnDM = false;
                    $nextDmVideosToCheck = [];
                    foreach ($dmVideosToCheck as $dmVideoToCheck) {
                        if ($dmVideoToCheck->getTitle() === $youtubeVideo->getTitle()) {
                            $isVideoUploadedOnDM = true;
                        } else {
                            $nextDmVideosToCheck[] = $dmVideoToCheck;
                        }
                    }

                    $dmVideosToCheck = $nextDmVideosToCheck;

                    if (! $isVideoUploadedOnDM) {
                        echo
                            PHP_EOL
                            . PHP_EOL
                            . 'Upload video'
                            . PHP_EOL
                            . '"'
                            . $youtubeVideo->getTitle()
                            . '" '
                            . PHP_EOL
                            . 'sur DailyMotion...'
                        ;
                        $token = $dmLogin->login($dmApiKey, $dmApiSecret, $dmUsername, $dmPassword);

                        if ($token === null) {
                            echo PHP_EOL . 'Erreur lors du login.';
                        }
                        // Upload to DM
                        // TODO upload video
                        die($token);
                    }
                }
            }
        }

        return 0;
    }
}
