<?php

namespace PierreMiniggio\YoutubeChannelCloner\Dailymotion;

use Dailymotion;
use Exception;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionApiLogin;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionFileUploader;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionUploadUrl;
use PierreMiniggio\YoutubeChannelCloner\Dailymotion\API\DailymotionVideoCreator;
use PierreMiniggio\YoutubeChannelCloner\Youtube\YoutubeVideo;

class DailymotionVideoUploaderIfNeeded
{

    private string $channelId;
    private string $username;
    private string $password;
    private string $apiKey;
    private string $apiSecret;

    private DailymotionApiLogin $login;
    private DailymotionUploadUrl $uploadUrlCreator;
    private DailymotionFileUploader $fileUploader;

    /** @var DailymotionVideo[] */
    private array $dmVideosToCheck;

    public function __construct(
        string $channelId,
        string $username,
        string $password,
        string $apiKey,
        string $apiSecret,
        LatestVideosFetcher $dmVideoFetcher
    )
    {
        $this->channelId = $channelId;
        $this->username = $username;
        $this->password = $password;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        $this->login = new DailymotionApiLogin();
        $this->uploadUrlCreator = new DailymotionUploadUrl();
        $this->fileUploader = new DailymotionFileUploader($apiKey, $apiSecret, $username, $password);

        $dmVideos = $dmVideoFetcher->fetch($channelId);
        $this->dmVideosToCheck = $dmVideos;
    }

    public function uploadIfNeeded(YoutubeVideo $youtubeVideo): void
    {
        $dmAPI = new Dailymotion();
        $dmAPI->setGrantType(
            Dailymotion::GRANT_TYPE_PASSWORD,
            $this->apiKey,
            $this->apiSecret,
            [
                'manage_videos'
            ],
            [
                'username' => $this->username,
                'password' => $this->password
            ]
        );
        $dmVideoCreator = new DailymotionVideoCreator($dmAPI);

        // Check if on DM
        $isVideoUploadedOnDM = false;
        $nextDmVideosToCheck = [];
        foreach ($this->dmVideosToCheck as $dmVideoToCheck) {
            if ($dmVideoToCheck->getTitle() === $youtubeVideo->getTitle()) {
                $isVideoUploadedOnDM = true;
            } else {
                $nextDmVideosToCheck[] = $dmVideoToCheck;
            }
        }

        $this->dmVideosToCheck = $nextDmVideosToCheck;

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
            $dmToken = $this->login->login(
                $this->apiKey,
                $this->apiSecret,
                $this->username,
                $this->password
            );

            if ($dmToken === null) {
                echo PHP_EOL . 'Erreur lors du login.';
            } else {
                $dmUploadUrl = $this->uploadUrlCreator->create($dmToken);
                if ($dmUploadUrl === null) {
                    echo PHP_EOL . 'Erreur lors de la création de l\'url d\'upload.';
                } else {
                    $dmVideoUrl = $this->fileUploader->upload($dmUploadUrl, $youtubeVideo->getSavedPath());
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
