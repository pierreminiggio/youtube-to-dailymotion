<?php

namespace PierreMiniggio\YoutubeToDailymotion\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class NonUploadedVideoRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findByDailymotionAndYoutubeChannelIds(int $dailymotionChannelId, int $youtubeChannelId): array
    {
        $this->connection->start();

        $uploadedDailyMotionVideoIds = $this->connection->query('
            SELECT d.id
            FROM dailymotion_video as d
            RIGHT JOIN dailymotion_video_youtube_video as dvyv
            ON d.id = dvyv.dailymotion_id
            WHERE d.channel_id = :channel_id
        ', ['channel_id' => $dailymotionChannelId]);
        $uploadedDailyMotionVideoIds = array_map(fn ($entry) => (int) $entry['id'], $uploadedDailyMotionVideoIds);

        $videosToUpload = $this->connection->query('
            SELECT
                y.id,
                y.youtube_id,
                y.url,
                y.title,
                y.sanitized_title,
                y.description
            FROM youtube_video as y
            ' . (
                $uploadedDailyMotionVideoIds
                    ? 'LEFT JOIN dailymotion_video_youtube_video as dvyv
                    ON y.id = dvyv.youtube_id
                    AND dvyv.dailymotion_id IN (' . implode(', ', $uploadedDailyMotionVideoIds) . ')'
                    : ''
            ) . '
            LEFT JOIN youtube_video_unpostable_on_dailymotion as yvuod
            ON yvuod.youtube_id = y.id
            
            WHERE y.channel_id = :channel_id
            AND yvuod.id IS NULL
            ' . ($uploadedDailyMotionVideoIds ? 'AND dvyv.id IS NULL' : '') . '
            ;
        ', [
            'channel_id' => $youtubeChannelId
        ]);
        $this->connection->stop();

        return $videosToUpload;
    }
}
