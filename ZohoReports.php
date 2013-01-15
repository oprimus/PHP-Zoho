<?php

include_once('ZohoReportsDatabase.php');

class ZohoReports extends Zoho {

    const FORMAT_JSON = "JSON";
    const FORMAT_CSV = "CSV";

    public $apiKey;
    public $username;
    public $password;
    public $ticket = null;
    public $fixedParams=array(
        'ZOHO_ERROR_FORMAT' => 'JSON',
        'ZOHO_API_VERSION' => '1.0'
    );
    public $pathPrefix = 'https://reportsapi.zoho.com/api';
    protected $databases = array();

    public function __construct($username, $password, $apiKey) {
        $this->username = $username;
        $this->password = $password;
        $this->apiKey = $apiKey;
    }

    public function call($owner, $database, $table, $action, $format, $params=array(), $options=array()) {

        $params = $this->fixedParams+array(
            'ZOHO_API_KEY' => $this->apiKey,
            'ZOHO_ACTION' => $action,
            'ZOHO_OUTPUT_FORMAT' => $format,
            'ticket' => $this->login($this->username, $this->password)
        )+$params;

        $url = $this->pathPrefix . '/' . urlencode($owner !== null ? $owner : $this->username);
        if ($database != null) {
            $url .= '/' . urlencode($database);
            if ($table != null) {
                $url .= '/' . urlencode($table);
            }
        }

        $output = $this->callUrl($url, $params, $options);
        if ($format === self::FORMAT_JSON) {
            return json_decode($output)->response->result;
        } else {
            return $output;
        }
    }

    public function database($name, $owner=null) {
        if (!array_key_exists($owner, $this->databases)) {
            $this->databases[$owner] = array();
        }
        if (!array_key_exists($name, $this->databases[$owner])) {
            $this->databases[$owner][$name] = new ZohoReportsDatabase($name, $owner, $this);
        }
        return $this->databases[$owner][$name];
    }

    public function databases($owner=null) {
        return $this->call($owner, null, null, 'DATABASEMETADATA', self::FORMAT_JSON, array('ZOHO_METADATA' => 'ZOHO_CATALOG_LIST'));
    }
}
