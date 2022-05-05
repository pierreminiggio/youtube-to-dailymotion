<?php

namespace PierreMiniggio\YoutubeToDailymotion;

use PierreMiniggio\MP4YoutubeVideoDownloader\Downloader;
use PierreMiniggio\YoutubeToDailymotion\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionAlreadyUploadedException;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionException;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionUnpostableVideoException;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\DailymotionVideoUploaderIfNeeded;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\LatestVideosFetcher as LatestDailymotionVideoFetcher;
use PierreMiniggio\YoutubeToDailymotion\Repository\LinkedChannelRepository;
use PierreMiniggio\YoutubeToDailymotion\Repository\NonUploadableVideoRepository;
use PierreMiniggio\YoutubeToDailymotion\Repository\NonUploadedVideoRepository;
use PierreMiniggio\YoutubeToDailymotion\Repository\VideoToUploadRepository;
use PierreMiniggio\YoutubeToDailymotion\Youtube\YoutubeVideo;

class App
{
    public function run(): int
    {
        $config = require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php');
        
        $youtubeVideoDownloader = new Downloader();
        $dmVideoFetcher = new LatestDailymotionVideoFetcher();
        $dailymotionMaxDescriptionLength = 3000;

        if (! empty($config['db'])) {
            $databaseConnection = (new DatabaseConnectionFactory())->makeFromConfig($config['db']);
            $channelRepository = new LinkedChannelRepository($databaseConnection);
            $nonUploadedVideoRepository = new NonUploadedVideoRepository($databaseConnection);
            $videoToUploadRepository = new VideoToUploadRepository($databaseConnection);
            $nonUploadableVideoRepository = new NonUploadableVideoRepository($databaseConnection);

            $channels = $channelRepository->findAll();
        
            foreach ($channels as $channel) {
                echo PHP_EOL . PHP_EOL . 'Checking channel ' . $channel['dailymotion_id'] . '...';

                try {

                    $videosToUpload = $nonUploadedVideoRepository->findByDailymotionAndYoutubeChannelIds($channel['d_id'], $channel['y_id']);

                    echo PHP_EOL . count($videosToUpload) . ' video(s) to upload :' . PHP_EOL;

                    if ($videosToUpload) {
                        $dmVideoUploaderIfNeeded = new DailymotionVideoUploaderIfNeeded(
                            $channel['dailymotion_id'],
                            $channel['username'],
                            $channel['password'],
                            $channel['api_key'],
                            $channel['api_secret'],
                            $channel['description_prefix'],
                            $youtubeVideoDownloader,
                            $dmVideoFetcher
                        );

                        foreach ($videosToUpload as $videoToUpload) {
                            echo PHP_EOL . 'Uploading ' . $videoToUpload['title'] . ' ...';
                            $youtubeDescription = $videoToUpload['description'];

                            if (strlen($youtubeDescription) > $dailymotionMaxDescriptionLength) {
                                $dailymotionDescription = '';
                                foreach (explode(' ', $youtubeDescription) as $wordIndex => $word) {
                                    $nextDailymotionDescription = $dailymotionDescription;
                                    if ($wordIndex > 0) {
                                        $nextDailymotionDescription .= ' ';
                                    }

                                   $nextDailymotionDescription .= $word;

                                   if (strlen($nextDailymotionDescription) > $dailymotionMaxDescriptionLength) {
                                       break;
                                   }
                                }
                            } else {
                                 $dailymotionDescription = $youtubeDescription;
                            }

                            try {
                                $uploadedVideoId = $dmVideoUploaderIfNeeded->uploadIfNeeded(new YoutubeVideo(
                                    $videoToUpload['youtube_id'],
                                    $videoToUpload['url'],
                                    $videoToUpload['title'],
                                    $videoToUpload['sanitized_title'],
                                    $dailymotionDescription
                                ));
                                $videoToUploadRepository->insertVideoIfNeeded($uploadedVideoId, $channel['d_id'], $videoToUpload['id']);
                                echo PHP_EOL . $videoToUpload['title'] . ' uploaded !';
                            } catch (DailymotionAlreadyUploadedException $e) {
                                $videoToUploadRepository->insertVideoIfNeeded($e->getVideoId(), $channel['d_id'], $videoToUpload['id']);
                                echo PHP_EOL . $videoToUpload['title'] . ' marked as uploaded !';
                            } catch (DailymotionUnpostableVideoException $e) {
                                $nonUploadableVideoRepository->markAsNonUploadableIfNeeded($videoToUpload['id']);
                                echo PHP_EOL . $videoToUpload['title'] . ' marked as non-uploadable !';
                            }
                        }
                    }
                } catch (DailymotionException $e) {
                    echo PHP_EOL . 'Error : ' . $e->getMessage();
                }

                echo PHP_EOL . PHP_EOL . 'Done for channel ' . $channel['dailymotion_id'] . ' !';
            }
        }

        return 0;
    }
}
