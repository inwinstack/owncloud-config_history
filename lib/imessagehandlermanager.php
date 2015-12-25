<?php

namespace OCA\Config_History;

interface IMessageHandlerManager {

    /**
     *
     * @param OCA\Config_History\IMessageHandler $messageHandler
     * @return void
     */
    public function registerMessageHandler(IMessageHandler $messageHandler);

}
