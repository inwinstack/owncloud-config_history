<?php

namespace OCA\Config_History\AppInfo;

$application = new Application();
$application->registerRoutes($this, ['routes' => [
    ['name' => 'ConfigHistory#fetch', 'url' => '/fetch', 'verb' => 'GET'],
]]);
