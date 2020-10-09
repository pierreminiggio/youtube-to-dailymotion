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

    private string $dmChannelId;
    private string $dmUsername;
    private string $dmPassword;
    private string $dmApiKey;
    private string $dmApiSecret;

    private DailymotionApiLogin $dmLogin;
    private DailymotionUploadUrl $dmUploadUrlCreator;
    private DailymotionFileUploader $dmFileUploader;

    /** @var DailymotionVideo[] */
    private array $dmVideosToCheck;

    public function __construct(
        string $dmChannelId,
        string $dmUsername,
        string $dmPassword,
        string $dmApiKey,
        string $dmApiSecret,
        LatestVideosFetcher $dmVideoFetcher
    )
    {
        $this->dmChannelId = $dmChannelId;
        $this->dmUsername = $dmUsername;
        $this->dmPassword = $dmPassword;
        $this->dmApiKey = $dmApiKey;
        $this->dmApiSecret = $dmApiSecret;

        $this->dmLogin = new DailymotionApiLogin();
        $this->dmUploadUrlCreator = new DailymotionUploadUrl();
        $this->dmFileUploader = new DailymotionFileUploader($dmApiKey, $dmApiSecret, $dmUsername, $dmPassword);

        $dmVideos = $dmVideoFetcher->fetch($dmChannelId);
        $this->dmVideosToCheck = $dmVideos;
    }

    public function uploadIfNeeded(YoutubeVideo $youtubeVideo): void
    {
        $dmAPI = new Dailymotion();
        $dmAPI->setGrantType(
            Dailymotion::GRANT_TYPE_PASSWORD,
            $this->dmApiKey,
            $this->dmApiSecret,
            [
                'manage_videos'
            ],
            [
                'username' => $this->dmUsername,
                'password' => $this->dmPassword
            ]
        );
        $dmVideoCreator = new DailymotionVideoCreator($dmAPI);

        if ($this->dmChannelId) {
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
                $dmToken = $this->dmLogin->login(
                    $this->dmApiKey,
                    $this->dmApiSecret,
                    $this->dmUsername,
                    $this->dmPassword
                );

                if ($dmToken === null) {
                    echo PHP_EOL . 'Erreur lors du login.';
                } else {
                    $dmUploadUrl = $this->dmUploadUrlCreator->create($dmToken);
                    if ($dmUploadUrl === null) {
                        echo PHP_EOL . 'Erreur lors de la création de l\'url d\'upload.';
                    } else {
                        $dmVideoUrl = $this->dmFileUploader->upload($dmUploadUrl, $youtubeVideo->getSavedPath());
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
