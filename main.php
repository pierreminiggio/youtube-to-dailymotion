<?php

require __DIR__ . '/vendor/autoload.php';

use PierreMiniggio\YoutubeChannelCloner\App;

try {
    return (new App($argv))->run();
} catch (Exception $e) {
    echo get_class($e) . ' : ' . $e->getMessage();
}
