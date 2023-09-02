<?php

// config/logger.config.php

use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;

return [
    'log' => [
        'writers' => [
            [
                'name' => Stream::class,
                'options' => [
                    'stream' => 'log/app.log', // Change the log path as needed
                ],
            ],
        ],
    ],
];
