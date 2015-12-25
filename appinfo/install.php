<?php
$state = \OCP\Config::getSystemValue('markup_configuration_history', 'doSet');
if($state === 'doSet') {
    \OCP\Config::setSystemValue('markup_configuration_history', true);
}
