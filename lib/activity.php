<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Config_History;

use OCP\IL10N;
use OCP\Util;
use OCP\Activity\IExtension;
use OCP\IConfig;

class Activity implements IExtension, IMessageHandlerManager {
	const CONFIG_HISTORY_ACTIVITY_APP = "config_history";

    const FILTER_CONFIG_HISTORY = "config_history";

    const TYPE_ADMIN_ACTIVITIES = "admin_operation";

	const SUBJECT_CREATE_VALUE = "create_value";
	const SUBJECT_UPDATE_VALUE = "update_value";

	/** @var IL10N */
	private $l;

	/** @var IMessageHandler */
	private $messageHandlers = array();
    
	/** @var IConfig */
	private $config;

	/**
     * @var IConfig
	 */
	public function __construct(IL10N $l10n, IConfig $config) {
		$this->l = $l10n;
		$this->config = $config;
	}

	public function getNotificationTypes($languageCode) {
        $l = Util::getL10N(self::CONFIG_HISTORY_ACTIVITY_APP, $languageCode);

        return [
            self::TYPE_ADMIN_ACTIVITIES => (string) $l->t("A admin operation has been done"),
        ];
	}

	/**
	 * For a given method additional types to be displayed in the settings can be returned.
	 * In case no additional types are to be added false is to be returned.
	 *
	 * @param string $method
	 * @return array|false
	 */
	public function getDefaultTypes($method) {
        if ($method === self::METHOD_STREAM) {
            $setting = array();
            $setting[] = self::TYPE_ADMIN_ACTIVITIES;
            return $setting;
        }
		return false;
	}

	/**
	 * The extension can translate a given message to the requested languages.
	 * If no translation is available false is to be returned.
	 *
	 * @param string $app
	 * @param string $text
	 * @param array $params
	 * @param boolean $stripPath
	 * @param boolean $highlightParams
	 * @param string $languageCode
	 * @return string|false
	 */
	public function translate($app, $text, $params, $stripPath, $highlightParams, $languageCode) {
        $handler = $this->findHandler($app);

        if($handler){
            $params = $handler->handle(array(
                "user" => $params[0],
                "key" => $params[1],
                "value" => $params[2],
            ), $app);
        }

        if($this->config->getSystemValue("markup_configuration_history")) {
            $params = $this->markupParams($params);
        }

        // params[1] is key.
        $params[1] = (string) $this->l->t($params[1]);
        
		switch ($text) {
            case self::SUBJECT_CREATE_VALUE:
				return (string) $this->l->t('%1$s create the value of %2$s to %3$s.', $params);
            case self::SUBJECT_UPDATE_VALUE:
				return (string) $this->l->t('%1$s update the value of %2$s to %3$s.', $params);
			default:
				return false;
		}
	}

	function getSpecialParameterList($app, $text) {
        return false;
	}

	public function getTypeIcon($type) {
        return false;
	}

	public function getGroupParameter($activity) {
		return false;
	}

	public function getNavigation() {
        return false;
	}

	public function isFilterValid($filterValue) {
		return $filterValue === self::FILTER_CONFIG_HISTORY;
	}

	public function filterNotificationTypes($types, $filter) {
        if ($filter === self::FILTER_CONFIG_HISTORY) {
            return array_intersect([self::TYPE_ADMIN_ACTIVITIES], $types);
        }
		return false;
	}

	public function getQueryForFilter($filter) {
		return false;
	}

    /*
     *
     * @param OCA\Config_History\IMessageHandler
     * @return void
     */
    public function registerMessageHandler(IMessageHandler $messageHandler) {
        if(!$messageHandler instanceof IMessageHandler) {
            return ;
        }
        $appName = $messageHandler->getAppName();
        $this->messageHandlers[$appName] = $messageHandler;
    }

    /*
     * @param Array
     * @return Array
     */
    private function markupParams($params) {
        foreach($params as $key => $param) {
            $params[$key] = "<strong>" . $param . "</strong>";
        }

        return $params;
    }

    /*
     * @param String
     * @return Array
     */
    private function findHandler($app) {
        if(array_key_exists($app, $this->messageHandlers)) {
            return $this->messageHandlers[$app];
        }

        if($app === "core") {
            return false;
        }

        return $this->messageHandlers["default"];
    }
}
