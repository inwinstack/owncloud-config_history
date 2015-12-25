<?php

namespace OCA\Config_History;

use OCP\IL10N;

class FilesExternalMessageHandler implements IMessageHandler {

    const MESSAGE_HANDLER_APP = "files_external";

    const SUBJECT_ALLOW_USER_MOUNTING = "allow_user_mounting";
    const SUBJECT_USER_MOUNTING_BACKENDS = "user_mounting_backends";

    protected $l;

    public static $backends = array(
        "\OC\Files\Storage\AmazonS3" => "Amazon S3 and compliant",
        "\OC\Files\Storage\Dropbox" => "Dropbox",
        "\OC\Files\Storage\FTP" => "FTP",
        "\OC\Files\Storage\Google" => "Google Drive",
        "\OC\Files\Storage\Swift" => "OpenStack Object Storage",
        "\OC\Files\Storage\OwnCloud" => "OwnCloud",
        "\OC\Files\Storage\SFTP" => "SFTP",
        "\OC\Files\Storage\SFTP_Key" => "SFTP with secret key login",
        "\OC\Files\Storage\SMB_OC" => "SMB / CIFS",
        "\OC\Files\Storage\SMB" => "SMB / CIFS using OC login",
        "\OC\Files\Storage\DAV" => "WebDAV"
    );

    public function __construct(IL10N $l) {
        $this->l = $l;
    }

    /*
     * @param Array
     * @param String
     * @return Array
     */
    public function handle($params, $appName = "") {
        switch($params["key"]) {
            case self::SUBJECT_USER_MOUNTING_BACKENDS:
                $params["value"] = $this->backendTranslate($params["value"]);
        }
        $params["key"] = DefaultMessageHandler::keyGenerator($params["key"], self::MESSAGE_HANDLER_APP);

        return array($params["user"], $params["key"], $params["value"]);
    }

    public function getAppName() {
        return self::MESSAGE_HANDLER_APP;
    }

    private function backendTranslate($backends) {
        $backends = explode(",", $backends);

        foreach($backends as $key => $backend) {
            $backend = self::$backends[$backend];
            $backends[$key] = $this->l->t($backend);
        }

        return implode(", ", $backends);
    }
}
