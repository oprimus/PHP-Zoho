<?php

include_once('ZohoCreatorApplication.php');

class ZohoCreator extends Zoho {

    protected $applications = array();

    public function __construct($authToken) {
        parent::__construct('creatorapi', 'https://creator.zoho.com/api', $authToken);
    }

    public function application($name) {
        if (!array_key_exists($name, $this->applications)) {
            $this->applications[$name] = new ZohoCreatorApplication($name, $this);
        }
        return $this->applications[$name];
    }

    /**
     * @see https://api.creator.zoho.com/REST-API-List-Applications.html
     */
    public function applications() {
        return $this->call('applications');
    }
}
