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

namespace OCA\Config_History\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;

use OCA\Activity\Data;
use OCA\Activity\GroupHelper;
use OCA\Activity\UserSettings;

class ConfigHistory extends Controller {

    const DEFAULT_PAGE_SIZE = 5;

    private $data;
    private $helper;
    private $setting;
    private $user;

	public function __construct($AppName, IRequest $request, Data $data, GroupHelper $helper, UserSettings $setting){
		parent::__construct($AppName, $request);
        $this->data = $data;
        $this->helper = $helper;
        $this->setting = $setting;
	}

    public function fetch($page, $filter = 'all') {
        $pageOffset = $page - 1;
        $filter = $this->data->validateFilter($filter);

        return new DataResponse($this->data->read($this->helper, $this->setting, $pageOffset * self::DEFAULT_PAGE_SIZE, self::DEFAULT_PAGE_SIZE, $filter));
    }
}
