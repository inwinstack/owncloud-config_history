<?php
namespace OCA\Config_History\AppInfo;

use OCP\AppFramework\App;

use OC\Files\View;
use OCA\Activity\Data;
use OCA\Activity\GroupHelper;
use OCA\Activity\UserSettings;
use OCA\Activity\DataHelper;
use OCA\Activity\ParameterHelper;

use OCA\Config_History\Activity;
use OCA\Config_History\ConfigHistoryAppConfig;
use OCA\Config_History\FilesExternalMessageHandler;
use OCA\Config_History\DefaultMessageHandler;
use OCA\Config_History\ConfigHistoryActivityManager;
use OCA\Config_History\Controller\ConfigHistory;

class Application extends App {

    public function __construct(array $urlParams=array()){
        parent::__construct("config_history", $urlParams);

        $container = $this->getContainer();

        $container->getServer()->registerService("AppConfig", function($c) use ($container){
            return new ConfigHistoryAppConfig(
                \OC_DB::getConnection(),
                $container->query("ActivityData"),
                $container->query("CurrentUID")
            );
        });

        $container->registerService("ActivityData", function($c) {
            $serverContainer = $c->getServer();
            return new Data(
                $c->query("ConfigHistoryActivityManager"),
                $serverContainer->getDatabaseConnection(),
                $serverContainer->getUserSession()
            );
        });

        $container->registerService("ConfigHistoryActivityManager", function($c) {
            $serverContainer = $c->getServer();
            return new ConfigHistoryActivityManager(
                $serverContainer->getRequest(),
                $serverContainer->getUserSession(),
                $serverContainer->getConfig()
            );
        });

        $container->registerService("CurrentUID", function($c) {
            $user = $c->getServer()->getUserSession()->getUser();

            return ($user) ? $user->getUID() : "";
        });

        $container->registerService("ConfigHistoryActivity", function($c) {
            return new Activity(
                $c->query("L10N"),
                $c->getServer()->getConfig()
            );
        });

        $container->registerService("L10N", function($c) {
            return $c->getServer()->getL10N("config_history");
        });

        $container->registerService("DataHelper", function($c) {
            $serverContainer = $c->getServer();
            return new DataHelper(
                $c->query("ConfigHistoryActivityManager"),
                new ParameterHelper (
                    $c->query("ConfigHistoryActivityManager"),
                    $serverContainer->getUserManager(),
                    $serverContainer->getURLGenerator(),
                    $serverContainer->getContactsManager(),
                    new View(""),
                    $serverContainer->getConfig(),
                    $c->query("L10N"),
                    $c->query("CurrentUID")
                ),
                $c->query("L10N")
            );
        });

        $container->registerService("GroupHelper", function($c) {
            return new GroupHelper(
                $c->query("ConfigHistoryActivityManager"),
                $c->query("DataHelper"),
                true
            );
        });

        $container->registerService("UserSettings", function($c) {
            $serverContainer = $c->getServer();
            return new UserSettings(
                $c->query("ConfigHistoryActivityManager"),
                $serverContainer->getConfig(),
                $c->query("ActivityData")
            );
        });


        $container->registerService("ConfigHistoryController", function($c) {
            return new ConfigHistory(
                $c->query("AppName"),
                $c->query("Request"),
                $c->query("ActivityData"),
                $c->query("GroupHelper"),
                $c->query("UserSettings")
            );
        });

        $container->query("ConfigHistoryActivityManager")->registerExtension(function() use ($container) {
            return $container->query("ConfigHistoryActivity");
        });

        $container->query("ConfigHistoryActivity")->registerMessageHandler(
            new FilesExternalMessageHandler(
                $container->query("L10N")
            )
        );

        $container->query("ConfigHistoryActivity")->registerMessageHandler(
            new DefaultMessageHandler(
                $container->query("L10N")
            )
        );
    }
}
