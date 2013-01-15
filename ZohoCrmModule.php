<?php

include_once('ZohoCrmModuleIterator.php');

class ZohoCrmModule {

    public $name;
    protected $zohoCrm;

    public $fixedParams = array(
        'version' => 2,
        'newFormat' => 2
    );

    public function __construct($name, ZohoCrm $zohoCrm) {
        $this->name = $name;
        $this->zohoCrm = $zohoCrm;
    }

    public function call($path, $params=array(), $options=array()) {
        return $this->zohoCrm->call("{$this->name}/$path", $params, $options);
    }

    public function compileSelectColumns($selectColumns) {
        if (empty($selectColumns) || $selectColumns === 'All') {
            return 'All';
        } else {
            return $this->name . '(' . implode(',', $selectColumns) . ')';
        }
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
}
