<?php

namespace PierreMiniggio\YoutubeToDailymotion\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class VideoToUploadRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function insertVideoIfNeeded(
        string $dailymotionId,
        int $dailymotionChannelId,
        int $youtubeChannelId
    ): void
    {
        $this->connection->start();
        $videoQueryParams = [
            'channel_id' => $dailymotionChannelId,
            'dailymotion_id' => $dailymotionId
        ];
        $findVideoIdQuery = ['
            SELECT id FROM dailymotion_video
            WHERE channel_id = :channel_id
            AND dailymotion_id = :dailymotion_id
            ;
        ', $videoQueryParams];
        $queriedIds = $this->connection->query(...$findVideoIdQuery);
        
        if (! $queriedIds) {
            $this->connection->exec('
                INSERT INTO dailymotion_video (channel_id, dailymotion_id)
                VALUES (:channel_id, :dailymotion_id)
            ', $videoQueryParams);
            $queriedIds = $this->connection->query(...$findVideoIdQuery);
        }

        $videoId = (int) $queriedIds[0]['id'];
        var_dump($videoId); die();
    }
}
