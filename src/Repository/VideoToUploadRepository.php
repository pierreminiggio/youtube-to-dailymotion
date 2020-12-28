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
        int $youtubeVideoId
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
                ;
            ', $videoQueryParams);
            $queriedIds = $this->connection->query(...$findVideoIdQuery);
        }

        $videoId = (int) $queriedIds[0]['id'];
        
        $pivotQueryParams = [
            'dailymotion_id' => $videoId,
            'youtube_id' => $youtubeVideoId
        ];

        $queriedPivotIds = $this->connection->query('
            SELECT id FROM dailymotion_video_youtube_video
            WHERE dailymotion_id = :dailymotion_id
            AND youtube_id = :youtube_id
            ;
        ', $pivotQueryParams);
        
        if (! $queriedPivotIds) {
            $this->connection->exec('
                INSERT INTO dailymotion_video_youtube_video (dailymotion_id, youtube_id)
                VALUES (:dailymotion_id, :youtube_id)
                ;
            ', $pivotQueryParams);
        }

        $this->connection->stop();
    }
}
