<?php

include_once('ZohoCrmModuleIterator.php');

class ZohoCrmModule {

    public $name;
    protected $zohoCrm;

    public $fixedParams = array(
        'version' => 2,
        'newFormat' => 2
    );

    // 'predefinedColumns' => https://www.zoho.com/crm/help/api/getsearchrecordsbypdc.html#Default_Predefined_Columns
    // TODO: This is incomplete....
    protected $moduleData = array(
        'Leads' => array(
            'predefinedColumns' => array('email')
        ),
        'Accounts' => array(
            'predefinedColumns' => array('accountid', 'accountname')
        ),
        'Contacts' => array(
            'predefinedColumns' => array('contactid', 'accountid', 'vendorid', 'email')
        ),
        'Products' => array(
            'predefinedColumns' => array('productid', 'vendorid', 'productname')
        ),
        'Notes' => array(
            'predefinedColumns' => array('noteid')
        )
    );

    public function __construct($name, ZohoCrm $zohoCrm) {
        $this->name = $name;
        $this->zohoCrm = $zohoCrm;
    }

    public function call($path, $params=array(), $options=array()) {
        return $this->zohoCrm->call("{$this->name}/$path", $params, $options);
    }

    public function callXml($path, $params=array(), $options=array()) {
        return $this->zohoCrm->callXml("{$this->name}/$path", $params, $options);
    }

    

    public function compileSelectColumns($selectColumns) {
        if (empty($selectColumns) || $selectColumns === 'All') {
            return 'All';
        } else {
            return $this->name . '(' . implode(',', $selectColumns) . ')';
        }
    }


    /**
     * Encode an array of records into a ZohoCRM 'XMLDATA' blob
     */
    public function encodeXmlData($records) {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(TRUE);
        $xml->startElement($this->name);
        foreach ($records as $idx => $row) {
            $xml->startElement('row');
            $xml->writeAttribute('no', $idx+1);
            foreach ($row as $val => $content) {
                $xml->startElement('FL');
                $xml->writeAttribute('val', $val);
                $xml->text($content); // CDATA?
                $xml->endElement();
            }
            $xml->endElement();
        }
        $xml->endDocument();
        return $xml->outputMemory();
    }

    /**
     * Decode a ZohoCRM 'XMLDATA' blob into an array of records
     */
    public function decodeXmlData($xmldata, $container=NULL) {
        if (NULL === $container) $container = $this->name;
        $xml = simplexml_load_string($xmldata);
        $records = array();
        assert($xml->getName() == $container);
        foreach ($xml->children() as $row) {
            $tmp = array();
            foreach ($row->FL as $fl) {
                $tmp[(string)$fl->attributes()->val] = (string)$fl;
            }
            $records[] = $tmp;
        }
        return $records;
    }


    /**
     * @see http://www.zoho.com/crm/help/api/getrecords.html
     */
    public function getRecords($options=array()) {
        if (array_key_exists('selectColumns', $options) && is_array($options['selectColumns'])) {
            $options['selectColumns'] = $this->compileSelectColumns($options['selectColumns']);
        }
        return new ZohoCrmModuleIterator($this, $options);
    }

    /**
     * getSearchRecords will automatically fall back to getSearchRecordsByPDC where possible
     * @see http://www.zoho.com/crm/help/api/getsearchrecords.html
     * @see http://www.zoho.com/crm/help/api/getsearchrecordsbypdc.html
     */
    public function getSearchRecords($column, $op, $value, $options=array()) {

        if (array_key_exists('selectColumns', $options) && is_array($options['selectColumns'])) {
            $options['selectColumns'] = $this->compileSelectColumns($options['selectColumns']);
        } else {
            $options['selectColumns'] = 'All';
        }

        if ($op == '=' && in_array(strtolower($column), $this->moduleData[$this->name]['predefinedColumns'])) {
            // We can use getSearchRecordsByPDC instead since it's "cheaper" in terms of API call limits
            $options['searchColumn'] = strtolower($column); // ByPDC is case sensitive, but the base method isn't...
            $options['searchValue' ] = $value;
            $options['_apiMethod'  ] = __FUNCTION__ . 'ByPDC';
        } else {
            // TODO: Can column/op/value be encoded? at least value could contain a '\'
            $options['searchCondition'] = "({$column}|${op}|{$value})";
            $options['_apiMethod'] = __FUNCTION__;
        }

        return new ZohoCrmModuleIterator($this, $options);
    }

    /**
     * @see http://www.zoho.com/crm/help/api/insertrecords.html
     */
    public function insertRecords($records) {
        $postData = array('xmlData' => $this->encodeXmlData($records));
        return $this->callXml(__FUNCTION__, array(), array('PostData' => $postData));
    }


    /**
     * @see https://www.zoho.com/crm/help/api/updaterecords.html#Update_with_Version4
     */
    public function updateRecords($records) {
        $postData = array('xmlData' => $this->encodeXmlData($records));
        return $this->callXml(__FUNCTION__, array('version' => 4), array('PostData' => $postData));
     }


    /**
     * @see http://www.zoho.com/crm/help/api/getusers.html
     */
    public function getUsers($type=ZohoCrmUserType::All) {
        if ($this->name != 'Users') {
            throw new Exception(sprintf('API Method "%s" is only available in the "Users" module', __FUNCTION__));
        } else {
            return $this->call(__FUNCTION__, array('type' => $type))->users->user;
        }
    }


}
