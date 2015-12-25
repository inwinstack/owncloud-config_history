<?php
/**
 * ownCloud - config_history
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author dauba <ab6060118@gmail.com>
 * @copyright dauba 2015
 */

namespace OCA\Config_History\AppInfo;

$app = new Application();

\OCP\App::registerAdmin('config_history', 'settings-admin');
