<?php

namespace OCA\Config_History;

use OCP\IL10N;

class DefaultMessageHandler implements IMessageHandler {
    const MESSAGE_HANDLER_APP = "default";

    protected $l;

    public function __construct(IL10N $l) {
        $this->l = $l;
    }

    /*
     * @param Array
     * @param String
     * @return Array
     */
    public function handle($params, $appName = "") {
        $params["key"] = self::keyGenerator($params["key"], $appName);

        return array($params["user"], $params["key"], $params["value"]);
    }

    public function getAppName() {
        return self::MESSAGE_HANDLER_APP;
    }

    /*
     *
     * @param String
     * @return String
     */
    public static function keyGenerator($key, $appName = "") {
        return $appName."_".$key;
    }
}
