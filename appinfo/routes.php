<?php

namespace OCA\Config_History\AppInfo;

$application = new Application();
$application->registerRoutes($this, ['routes' => [
    ['name' => 'ConfigurationHistory#fetch', 'url' => '/fetch', 'verb' => 'GET'],
]]);
