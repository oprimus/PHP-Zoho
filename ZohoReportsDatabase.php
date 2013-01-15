<?php

class ZohoReportsDatabase {

    const IMPORTTYPE_APPEND = "APPEND";
    const IMPORTTYPE_TRUNCATEADD = "TRUNCATEADD";
    const IMPORTTYPE_UPDATEADD = "UPDATEADD";

    const IMPORTERROR_ABORT = "ABORT";
    const IMPORTERROR_SKIPROW = "SKIPROW";
    const IMPORTERROR_SETCOLUMNEMPTY = "SETCOLUMNEMPTY";

    public $name;
    public $owner;
    protected $zohoReports;

    public function __construct($name, $owner, ZohoReports $zohoReports) {
        $this->name = $name;
        $this->owner = $owner;
        $this->zohoReports = $zohoReports;
    }

    public function call($tableName, $action, $format, $params=array(), $options=array()) {
        return $this->zohoReports->call($this->owner, $this->name, $tableName, $action, $format, $params, $options);
    }

    /**
     * @see http://zohoreportsapi.wiki.zoho.com/Applying-Filters.html
     */
    protected function compileCriteria($criteria, $isAnd=true) {
        if (is_array($criteria)) {
            $parts = array();
            foreach ($criteria as $key => $val) {
                if (is_array($val)) {
                    $parts[] = '(' . $this->compileCriteria($val, !$isAnd) . ')';
                } else {
                    $parts[] = '"' . addslashes($key) . '"=\'' . addslashes($val) . '\'';
                }
            }
            return implode(($isAnd ? ' and ' : ' or '), $parts);
        } else {
            return $criteria;
        }
    }
    
    /**
     * @see http://zohoreportsapi.wiki.zoho.com/Export.html
     */
    public function export($tableName, $format=ZohoReports::FORMAT_JSON, $criteria=null, $params=array()) {
        $options = array();
        if ($criteria !== null) {
            $options['PostData'] = array('ZOHO_CRITERIA' => $this->compileCriteria($criteria));
        }
        return $this->call($tableName, 'EXPORT', $format, $params, $options);
    }
    
    /**
     * @see https://zohoreportsapi.wiki.zoho.com/Importing-CSV-File.html
     */
    public function importCsv($tableName, $path, $importType=self::IMPORTTYPE_APPEND, $onError=self::IMPORTERROR_ABORT, $autoIdentify=true, $params=array()) {
        if (!is_readable($path)) {
            throw new Exception('Unabled to read file: ' . $path);
        }
        $options = array('PostData' => $params+array(
            'ZOHO_AUTO_IDENTIFY' => ($autoIdentify ? 'true' : 'false'),
            'ZOHO_ON_IMPORT_ERROR' => $onError,
            'ZOHO_IMPORT_TYPE' => $importType,
            'ZOHO_FILE' => '@' . $path
        ));
        return $this->call($tableName, 'IMPORT', ZohoReports::FORMAT_JSON, array(), $options);
    }
}
