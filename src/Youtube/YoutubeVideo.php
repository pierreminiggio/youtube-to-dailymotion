<?php

namespace PierreMiniggio\YoutubeToDailymotion\Youtube;

class YoutubeVideo
{

    private string $id;
    private string $url;
    private string $title;
    private string $sanitizedTitle;
    private string $description;

    public function __construct(
        string $id,
        string $url,
        string $title,
        string $sanitizedTitle,
        string $description
    )
    {
        $this->id = $id;
        $this->url = $url;
        $this->title = $title;
        $this->sanitizedTitle = $sanitizedTitle;
        $this->description = $description;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    private function getSanitizedTitle(): string
    {
        return $this->sanitizedTitle;
    }

    public function getSavedPath(): string
    {
        return
            __DIR__
            . DIRECTORY_SEPARATOR
            . '..'
            . DIRECTORY_SEPARATOR
            . '..'
            . DIRECTORY_SEPARATOR
            . 'videos'
            . DIRECTORY_SEPARATOR
            . $this->getSanitizedTitle()
            . '.mp4'
        ;
    }
}
