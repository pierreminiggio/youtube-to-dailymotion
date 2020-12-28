<?php

namespace PierreMiniggio\YoutubeToDailymotion\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class LinkedChannelRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findAll(): array
    {
        $this->connection->start();
        $channels = $this->connection->query('
            SELECT
                dcyc.youtube_id as y_id,
                d.id as d_id,
                d.dailymotion_id,
                d.username,
                d.password,
                d.api_key,
                d.api_secret,
                d.description_prefix
            FROM dailymotion_channel as d
            RIGHT JOIN dailymotion_channel_youtube_channel as dcyc
                ON d.id = dcyc.dailymotion_id
        ', []);
        $this->connection->stop();

        return $channels;
    }
}
