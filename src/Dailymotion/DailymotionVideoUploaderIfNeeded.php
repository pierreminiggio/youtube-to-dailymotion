<?php

namespace PierreMiniggio\YoutubeChannelCloner\Dailymotion;

use Exception;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionApiLogin;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionFileUploader;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionUploadUrl;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionVideoCreator;
use PierreMiniggio\YoutubeChannelCloner\Youtube\YoutubeVideo;

class DailymotionVideoUploaderIfNeeded
{
    /**
     * @param DailymotionVideo[]
     */
    public function uploadIfNeeded(
        YoutubeVideo $youtubeVideo,
        string $dmChannelId,
        array $dmVideosToCheck,
        DailymotionApiLogin $dmLogin,
        string $dmApiKey,
        string $dmApiSecret,
        string $dmUsername,
        string $dmPassword,
        DailymotionUploadUrl $dmUploadUrlCreator,
        DailymotionFileUploader $dmFileUploader,
        DailymotionVideoCreator $dmVideoCreator
    ): void
    {
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
                $dmToken = $dmLogin->login($dmApiKey, $dmApiSecret, $dmUsername, $dmPassword);

                if ($dmToken === null) {
                    echo PHP_EOL . 'Erreur lors du login.';
                } else {
                    $dmUploadUrl = $dmUploadUrlCreator->create($dmToken);
                    if ($dmUploadUrl === null) {
                        echo PHP_EOL . 'Erreur lors de la création de l\'url d\'upload.';
                    } else {
                        $dmVideoUrl = $dmFileUploader->upload($dmUploadUrl, $youtubeVideo->getSavedPath());
                        if ($dmVideoUrl === null) {
                            echo PHP_EOL . 'Erreur lors de l\'upload de la vidéo temporaire.';
                        } else {
                            try {
                                $dmVideoId = $dmVideoCreator->create(
                                    $dmVideoUrl,
                                    $youtubeVideo->getTitle(),
                                    'Vidéo disponible sur Youtube : ' . $youtubeVideo->getUrl() . '

' . $youtubeVideo->getDescription()
                                );

                                if ($dmVideoId) {
                                    echo PHP_EOL . 'Vidéo uploadée !';
                                }
                            } catch (Exception $e) {
                                echo PHP_EOL
                                    . 'Erreur lors de la création de la vidéo : "'
                                    . PHP_EOL
                                    . $e->getMessage()
                                    . '"'
                                ;
                            }
                        }
                    }
                }
            }
        }
    }
}
