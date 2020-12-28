<?php

namespace PierreMiniggio\YoutubeToDailymotion\Dailymotion;

use Dailymotion;
use Exception;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionAlreadyUploadedException;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionApiLogin;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionException;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionFileUploader;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionUnpostableVideoException;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionUploadUrl;
use PierreMiniggio\YoutubeToDailymotion\Dailymotion\API\DailymotionVideoCreator;
use PierreMiniggio\YoutubeToDailymotion\Youtube\VideoFileDownloader;
use PierreMiniggio\YoutubeToDailymotion\Youtube\YoutubeVideo;

class DailymotionVideoUploaderIfNeeded
{

    private string $username;
    private string $password;
    private string $apiKey;
    private string $apiSecret;
    private string $descriptionPrefix;
    private VideoFileDownloader $downloader;

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
        VideoFileDownloader $downloader,
        LatestVideosFetcher $dmVideoFetcher
    )
    {
        $this->username = $username;
        $this->password = $password;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->descriptionPrefix = $descriptionPrefix;
        $this->downloader = $downloader;

        $this->login = new DailymotionApiLogin();
        $this->uploadUrlCreator = new DailymotionUploadUrl();
        $this->fileUploader = new DailymotionFileUploader($apiKey, $apiSecret, $username, $password);

        $dmVideos = $dmVideoFetcher->fetch($channelId);
        $this->dmVideosToCheck = $dmVideos;
    }

    /**
     * @throws DailymotionAlreadyUploadedException
     * @throws DailymotionException
     * @throws DailymotionUnpostableVideoException
     */
    public function uploadIfNeeded(YoutubeVideo $youtubeVideo): string
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

        $dmToken = $this->login->login(
            $this->apiKey,
            $this->apiSecret,
            $this->username,
            $this->password
        );

        if ($dmToken === null) {
            throw new DailymotionException('Error while logging in.');
        }

        $dmUploadUrl = $this->uploadUrlCreator->create($dmToken);

        if ($dmUploadUrl === null) {
            throw new DailymotionException('Error while creating upload URL.');
        }

        $this->downloadVideoIfNeeded($youtubeVideo);
        $dmVideoUrl = $this->fileUploader->upload($dmUploadUrl, $youtubeVideo->getSavedPath());

        if ($dmVideoUrl === null) {
            throw new DailymotionException('Error while temporary video upload.');
        }

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
            $this->dropVideoFileIfPresent($youtubeVideo);

            return $dmVideoId;
        } catch (Exception $e) {

            if (str_contains($e->getMessage(), 'Duration of this video is too long')) {
                $this->dropVideoFileIfPresent($youtubeVideo);
                throw new DailymotionUnpostableVideoException();
            }

            throw new DailymotionException(
                'Error while creating the video :' . PHP_EOL . '"'
                . $e->getMessage()
                . '"'
            );
        }
    }

    private function downloadVideoIfNeeded(YoutubeVideo $video): void
    {
        $videoFilePath = $video->getSavedPath();
        if (! file_exists($videoFilePath)) {
            $this->downloader->download(
                $video->getUrl(),
                $videoFilePath
            );
        }
    }

    private function dropVideoFileIfPresent(YoutubeVideo $video): void
    {
        $videoFilePath = $video->getSavedPath();
        if (file_exists($videoFilePath)) {
            unlink($videoFilePath);
        }
    }
}
