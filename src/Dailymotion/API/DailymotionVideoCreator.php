<?php

namespace PierreMiniggio\YoutubeToDailymotion\Dailymotion\API;

use Dailymotion;
use Exception;

class DailymotionVideoCreator
{

    private Dailymotion $api;

    public function __construct(DailyMotion $dm)
    {
        $this->api = $dm;
    }

    /**
     * @throws Exception
     */
    public function create(string $videoUrl, string $videoTitle, string $videoDescription): string
    {
        $res = $this->api->post(
            '/videos',
            [
                'url' => $videoUrl,
                'title' => $videoTitle,
                'description' => $videoDescription,
                'tags' => 'developpement,informatique,découverte,éducation',
                'channel' => 'school',
                'published' => true
            ]
        );

        if (isset($res) && isset($res['id'])) {
            return $res['id'];
        }
    }
}
