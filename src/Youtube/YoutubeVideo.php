<?php

namespace PierreMiniggio\YoutubeChannelCloner\Youtube;

class YoutubeVideo
{

    private string $channel;
    private string $id;
    private string $url;
    private string $thumbnail;
    private string $title;
    private string $description;

    public function __construct(
        string $channel,
        string $id,
        string $url,
        string $thumbnail,
        string $title,
        string $description
    )
    {
        $this->channel = $channel;
        $this->id = $id;
        $this->url = $url;
        $this->thumbnail = $thumbnail;
        $this->title = $title;
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

    public function getThumbnail(): string
    {
        return $this->thumbnail;
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
        return str_replace('.', '', mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $this->title));
    }

    public function getSavedPath(): string
    {
        return
            getcwd()
            . DIRECTORY_SEPARATOR
            . 'videos'
            . DIRECTORY_SEPARATOR
            . $this->channel
            . DIRECTORY_SEPARATOR
            . $this->getSanitizedTitle()
            . '.mp4'
        ;
    }
}
