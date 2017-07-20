<?php

class ZohoCreatorApplication {

    public $name;
    protected $zohoCreator;
    protected $views = array();

    public function __construct($name, ZohoCreator $zohoCreator) {
        $this->name = $name;
        $this->zohoCreator = $zohoCreator;
    }

    public function call($path, $params=array(), $options=array(), $ownerInUrl=false) {
        return $this->zohoCreator->call("{$this->name}/$path", $params, $options, $ownerInUrl);
    }

    /**
     * @see https://api.creator.zoho.com/REST-API-View-Records-in-View.html
     */
    public function viewRecords($viewName) {
        return $this->call("view/{$viewName}", array('raw' => 'true'));
    }

    /**
     * @see https://api.creator.zoho.com/REST-API-List-Forms-and-Views.html
     */
    public function formsAndViews() {
        return $this->call("formsandviews");
    }

    /**
     * @see https://api.creator.zoho.com/REST-API-Add-Records.html
     * @param array $data An associative array of key => value pairs to set
     */
    public function add($formName, $data) {
        $result = $this->call("form/{$formName}/record/add/", array(), array("PostData" => $data), true);
        if ($result->formname[1]->operation[1]->status == 'Success') {
            return $result->formname[1]->operation[1]->status;
        } else {
            throw new Exception(sprintf("Zoho error: %s", $result->formname[1]->operation[1]->status));
        }
    }

}
