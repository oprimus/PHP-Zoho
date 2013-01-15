<?php

include_once('ZohoCreator.php');
include_once('ZohoCrm.php');
include_once('ZohoReports.php');

abstract class Zoho {

    public $scope;
    public $authToken;
    public $pathPrefix;
    public $proxy;
    public $fixedParams=array();
    public $ticket=null;

    public function __construct($scope, $pathPrefix, $authToken) {
        $this->scope = $scope;
        $this->pathPrefix = $pathPrefix;
        $this->authToken = $authToken;
    }

    public function setProxy($proxy) {
        $this->proxy = $proxy;
    }

    public function __destruct() {
        $this->logout();
    }

    public function parseAccountAction($str) {
        $result = array();
        foreach (split("\r?\n", $str) as $line) {
            if (substr(trim($line), 0, 1) == '#') continue;
            if (preg_match('/([a-zA-Z]+)=(.+)/', trim($line), $m)) {
                $result[$m[1]] = $m[2];
            }
        }
        return $result;
    }

    public function login($username, $password) {
        if ($this->ticket === null ) {
            $postData = array(
                'LOGIN_ID' => $username,
                'PASSWORD' => $password,
                'FROM_AGENT' => 'true',
                'servicename' => 'ZohoReports'
            );
            $resultStr = $this->callUrl('https://accounts.zoho.com/login', array(), array('PostData' => $postData));
            $result = $this->parseAccountAction($resultStr);
            if (array_key_exists('RESULT', $result) && array_key_exists('TICKET', $result) && $result['RESULT'] == 'TRUE') {
                $this->ticket = $result['TICKET'];
            } else {
                throw new Exception("Unable to login to Zoho: " . $resultStr);
            }
        }
        return $this->ticket;
    }

    public function logout() {
        if ($this->ticket !== null ) {
            $postData = array(
                'ticket' => $this->ticket,
                'FROM_AGENT' => 'true'
            );
            $resultStr = $this->callUrl('https://accounts.zoho.com/logout', array(), array('PostData' => $postData));
            $result = $this->parseAccountAction($resultStr);
            if (array_key_exists('RESULT', $result) && $result['RESULT'] == 'TRUE') {
                return true;
            } else {
                throw new Exception("Unable to logout of Zoho: " . $resultStr);
            }
        }
        return false;
    }

    public function call($path, $params=array(), $options=array()) {

        $params = $this->fixedParams+array('scope' => $this->scope, 'authtoken' => $this->authToken)+$params;
        $url = $this->pathPrefix . '/json/' . $path;
        return json_decode($this->call($url, $params, $options));
    }

    public function callUrl($url, $params=array(), $options=array()) {

        // Initialise CURL
        $ch = curl_init();
        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        // Post data
        if (array_key_exists('PostData', $options) && is_array($options['PostData'])) {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($options['PostData'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $options['PostData']);
            }
        }

        // Build URL
        $paramparts = array();
        foreach ($params as $key => $val) {
            $paramparts[] = urlencode($key) . '=' . urlencode($val);
        }
        $url = $url;
        if (!empty($paramparts)) {
            $url .= '?' . implode('&', $paramparts);
        }
        curl_setopt($ch, CURLOPT_URL, $url);

        // Call
        $output = curl_exec($ch);

        // Analyse result
        if ($output === false) {
            throw new Exception(sprintf("CURL error (#%d) \"%s\". Requested URL was \"%s\".", curl_errno($ch), curl_error($ch), $url));
        }
        curl_close($ch);

        return $output;
    }
}
