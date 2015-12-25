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

use OC\L10N\Factory;
use OCP\Activity\IExtension;
use OCP\IURLGenerator;
use OCP\IConfig;

class Activity implements IExtension, IMessageHandlerManager {
	const ADMIN_ACTIVITY_APP = "config_history";

    const FILTER_ADMIN_ACTIVITIES = "configuration_history";

    const TYPE_ADMIN_ACTIVITIES = "admin_operation";

	const SUBJECT_CREATE_VALUE = "create_value";
	const SUBJECT_UPDATE_VALUE = "update_value";

	/** @var IL10N */
	protected $l;

	/** @var Factory */
	protected $languageFactory;

	/** @var IURLGenerator */
	protected $URLGenerator;

	/** @var IMessageHandler */
	protected $messageHandlers = array();
    
	/** @var IConfig */
	protected $config;

	/**
	 * @param Factory $languageFactory
	 * @param IURLGenerator $URLGenerator
	 */
	public function __construct(Factory $languageFactory, IURLGenerator $URLGenerator, IConfig $config) {
		$this->languageFactory = $languageFactory;
		$this->l = $this->getL10N();
		$this->URLGenerator = $URLGenerator;
		$this->config = $config;
	}

	/**
	 * @param string|null $languageCode
	 * @return IL10N
	 */
	protected function getL10N($languageCode = null) {
		return $this->languageFactory->get(self::ADMIN_ACTIVITY_APP, $languageCode);
	}

	/**
	 * The extension can return an array of additional notification types.
	 * If no additional types are to be added false is to be returned
	 *
	 * @param string $languageCode
	 * @return array|false
	 */
	public function getNotificationTypes($languageCode) {
		$l = $this->getL10N($languageCode);
		return [
			self::TYPE_ADMIN_ACTIVITIES => (string) $l->t("A new file or folder has been <strong>created</strong>"),
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
		if ($method === "stream") {
			$settings = array();
			$settings[] = self::TYPE_ADMIN_ACTIVITIES;
			return $settings;
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

	/**
	 * The extension can define the type of parameters for translation
	 *
	 * Currently known types are:
	 * * file		=> will strip away the path of the file and add a tooltip with it
	 * * username	=> will add the avatar of the user
	 *
	 * @param string $app
	 * @param string $text
	 * @return array|false
	 */
	function getSpecialParameterList($app, $text) {
        return false;
	}

	/**
	 * A string naming the css class for the icon to be used can be returned.
	 * If no icon is known for the given type false is to be returned.
	 *
	 * @param string $type
	 * @return string|false
	 */
	public function getTypeIcon($type) {
        return false;
	}

	/**
	 * The extension can define the parameter grouping by returning the index as integer.
	 * In case no grouping is required false is to be returned.
	 *
	 * @param array $activity
	 * @return integer|false
	 */
	public function getGroupParameter($activity) {
		return false;
	}

	/**
	 * The extension can define additional navigation entries. The array returned has to contain two keys "top"
	 * and "apps" which hold arrays with the relevant entries.
	 * If no further entries are to be added false is no be returned.
	 *
	 * @return array|false
	 */
	public function getNavigation() {
        return false;
	}

	/**
	 * The extension can check if a customer filter (given by a query string like filter=abc) is valid or not.
	 *
	 * @param string $filterValue
	 * @return boolean
	 */
	public function isFilterValid($filterValue) {
		return $filterValue === self::FILTER_ADMIN_ACTIVITIES;
	}

	/**
	 * The extension can filter the types based on the filter if required.
	 * In case no filter is to be applied false is to be returned unchanged.
	 *
	 * @param array $types
	 * @param string $filter
	 * @return array|false
	 */
	public function filterNotificationTypes($types, $filter) {
		if ($filter === self::FILTER_ADMIN_ACTIVITIES) {
			return array_intersect([ self::TYPE_ADMIN_ACTIVITIES], $types);
		}
		return false;
	}

	/**
	 * For a given filter the extension can specify the sql query conditions including parameters for that query.
	 * In case the extension does not know the filter false is to be returned.
	 * The query condition and the parameters are to be returned as array with two elements.
	 * E.g. return array("`app` = ? and `message` like ?", array("mail", "ownCloud%"));
	 *
	 * @param string $filter
	 * @return array|false
	 */
	public function getQueryForFilter($filter) {
		return false;
	}

    /*
     *
     * @param OCA\Config_History\IMessageHandler
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
