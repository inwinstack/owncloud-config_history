<?php

namespace OCA\Config_History;

use OC\AppConfig;
use OC\DB\Connection;
use OC\Activity\Event;

use OCA\Activity\Data;

use OCP\User;

class ConfigHistoryAppConfig extends AppConfig{

    private $exceptionKeys = ["/core_lastcron/", "/core_lastjob/", "/core_lastupdateResult/", "/core_lastupdatedat/", "/^files_external_\/\w+/"];
    private $data;
    private $currentUID;

    public function __construct(Connection $conn, Data $data, $UID) {
        parent::__construct($conn);
        $this->data = $data;
        $this->currentUID = $UID;
    }

    public function setValue($app, $key, $value) {
        $type = Extension\ConfigHistory::ADMIN_OPERATION;
        $user = User::getUser();
        $inserted = false; 

        if (!$this->hasKey($app, $key)) {
            $inserted = (bool) $this->conn->insertIfNotExist("*PREFIX*appconfig", [
                "appid" => $app,
                "configkey" => $key,
                "configvalue" => $value,
            ], [
                "appid",
                "configkey",
            ]);
        }

        if (!$inserted) {
            $subject = "update_value";
        }
        else {
            $subject = "create_value";
        }

        if(!$this->match($app, $key)) {
            $usersInGroup = \OC_Group::usersInGroup("admin");
            foreach($usersInGroup as $affecteduser) {
                $event = new Event();
                $event->setApp($app)
                    ->setType($type)
                    ->setAffectedUser($user)
                    ->setAuthor($this->currentUID)
                    ->setTimestamp(time())
                    ->setSubject($subject, array($user, $key, $value));
                $this->data->send($event);
            }
        }

        parent::setValue($app, $key, $value);
    }

    private function match($app, $key) {
        foreach($this->exceptionKeys as $exceptionKey) {
            if(preg_match($exceptionKey, $app . "_" . $key) == 1){
                return true;
            }
        }

        return false;
    }
}
