<?php

class ZohoCreatorApplication {

    public $name;
    protected $zohoCreator;
    protected $views = array();

    public function __construct($name, ZohoCreator $zohoCreator) {
        $this->name = $name;
        $this->zohoCreator = $zohoCreator;
    }

    public function call($path, $params=array(), $options=array()) {
        return $this->zohoCreator->call("{$this->name}/$path", $params, $options);
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
        $result = $this->call("{$formName}/add/", array(), array("PostData" => $data));
        if ($result->formname[1]->operation[1]->values[1]->status[0] == 'Success') {
            return $result->formname[1]->operation[1]->values[0];
        } else {
            throw new Exception(sprintf("Zoho error: %s", $result->formname[1]->operation[1]->values[1]->status[0]));
        }
    }

}
