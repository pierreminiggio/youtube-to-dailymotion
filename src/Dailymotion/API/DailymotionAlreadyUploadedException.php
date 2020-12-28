<?php

namespace PierreMiniggio\YoutubeToDailymotion\Dailymotion\API;

use Exception;
use Throwable;

class DailymotionAlreadyUploadedException extends Exception
{

    private string $videoId;

    public function __construct(string $videoId, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->videoId = $videoId;
        parent::__construct($message, $code, $previous);
    }

    public function getVideoId(): string
    {
        return $this->videoId;
    }
}
