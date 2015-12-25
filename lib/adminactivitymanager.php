<?php

namespace OCA\Config_History;

use OC\ActivityManager;

use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class AdminActivityManager extends ActivityManager {

	/**
	 * @var \Closure[]
	 */
	private $consumers = array();

	/**
	 * @var \Closure[]
	 */
	private $extensions = array();

	/** @var array list of filters "name" => "is valid" */
	protected $validFilters = array(
		"all"	=> true,
		"by"	=> true,
		"self"	=> true,
	);

    public function __construct(IRequest $request, IUserSession $session, IConfig $config) {
        parent::__construct($request, $session, $config);
    }
}
