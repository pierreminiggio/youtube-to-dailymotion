<?php

namespace PierreMiniggio\YoutubeToDailymotion\Dailymotion;

use Dailymotion;
use Exception;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionAlreadyUploadedException;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionApiLogin;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionFileUploader;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionUploadUrl;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionVideoCreator;
use PierreMiniggio\YoutubeToDailymotion\Youtube\YoutubeVideo;

class DailymotionVideoUploaderIfNeeded
{

    private string $username;
    private string $password;
    private string $apiKey;
    private string $apiSecret;
    private string $descriptionPrefix;

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
        string $descriptionPrefix,
        LatestVideosFetcher $dmVideoFetcher
    )
    {
        $this->username = $username;
        $this->password = $password;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->descriptionPrefix = $descriptionPrefix;

        $this->login = new DailymotionApiLogin();
        $this->uploadUrlCreator = new DailymotionUploadUrl();
        $this->fileUploader = new DailymotionFileUploader($apiKey, $apiSecret, $username, $password);

        $dmVideos = $dmVideoFetcher->fetch($channelId);
        $this->dmVideosToCheck = $dmVideos;
    }

    /**
     * @throws DailymotionAlreadyUploadedException
     * @throws DailymotionException
     */
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
        $alreadyUploadedVideoId = null;

        foreach ($this->dmVideosToCheck as $dmVideoToCheck) {
            if ($dmVideoToCheck->getTitle() === $youtubeVideo->getTitle()) {
                $isVideoUploadedOnDM = true;
                $alreadyUploadedVideoId = $dmVideoToCheck->getId();
            } else {
                $nextDmVideosToCheck[] = $dmVideoToCheck;
            }
        }

        $this->dmVideosToCheck = $nextDmVideosToCheck;

        if ($isVideoUploadedOnDM) {
            throw new DailymotionAlreadyUploadedException($alreadyUploadedVideoId);
        }

        die('on upload !');

        echo
            PHP_EOL
            . PHP_EOL
            . 'Uploading video'
            . PHP_EOL
            . '"'
            . $youtubeVideo->getTitle()
            . '" '
            . PHP_EOL
            . 'to DailyMotion...'
        ;
        $dmToken = $this->login->login(
            $this->apiKey,
            $this->apiSecret,
            $this->username,
            $this->password
        );

        if ($dmToken === null) {
            echo PHP_EOL . 'Error while logging in.';
        } else {
            $dmUploadUrl = $this->uploadUrlCreator->create($dmToken);
            if ($dmUploadUrl === null) {
                echo PHP_EOL . 'Error while creating upload URL.';
            } else {
                $dmVideoUrl = $this->fileUploader->upload($dmUploadUrl, $youtubeVideo->getSavedPath());
                if ($dmVideoUrl === null) {
                    echo PHP_EOL . 'Erreur while temporary video upload.';
                } else {
                    try {
                        $dmVideoId = $dmVideoCreator->create(
                            $dmVideoUrl,
                            $youtubeVideo->getTitle(),
                            (! empty($this->descriptionPrefix) ? str_replace(
                                '[youtube_url]',
                                $youtubeVideo->getUrl(),
                                $this->descriptionPrefix
                            ) : '')
                            . $youtubeVideo->getDescription()
                        );

                        if ($dmVideoId) {
                            echo PHP_EOL . 'Video uploaded !';
                        }
                    } catch (Exception $e) {
                        echo PHP_EOL
                            . 'Error while creating the video : "'
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
