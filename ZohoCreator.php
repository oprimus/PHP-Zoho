<?php

include_once('ZohoCreatorApplication.php');

class ZohoCreator extends Zoho {

    protected $applications = array();
    public $ownerName;

    public function __construct($authToken, $ownerName) {
        $this->ownerName = $ownerName;
        parent::__construct('creatorapi', 'https://creator.zoho.com/api', $authToken);
    }

    public function call($path, $params=array(), $options=array(), $ownerInUrl=false) {
        $params = $this->fixedParams+array('scope' => $this->scope, 'authtoken' => $this->authToken)+$params;
       if ($ownerInUrl) {
            $url = $this->pathPrefix . '/' . $this->ownerName . '/json/' . $path;
        } else {
       //     $params['zc_ownername'] = $this->ownerName;
            $url = $this->pathPrefix . '/json/' . $path;
        }
        return json_decode($this->callUrl($url, $params, $options));
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
        return $this->call('applications', array('zc_ownername' => $this->ownerName));
    }
}
