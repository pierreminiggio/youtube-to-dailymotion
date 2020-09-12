<?php

namespace PierreMiniggio\YoutubeChannelCloner\Youtube;

class YoutubeVideo
{

    private string $id;
    private string $url;
    private string $thumbnail;
    private string $title;
    private string $description;

    public function __construct(
        string $id,
        string $url,
        string $thumbnail,
        string $title,
        string $description
    )
    {
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
}
