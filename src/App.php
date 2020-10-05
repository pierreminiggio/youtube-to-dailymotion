<?php

namespace PierreMiniggio\YoutubeChannelCloner;

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

            if (isset($group['dailymotion']) && isset($group['dailymotion']['channelId'])) {
                $dmChannelId = $group['dailymotion']['channelId'];
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

                        var_dump($youtubeVideo->getTitle());
                        // Upload to DM
                        // TODO upload video

                    }
                }
            }
        }

        return 0;
    }
}
