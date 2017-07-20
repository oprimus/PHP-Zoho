<?php

class ZohoCrmModuleIterator implements Iterator {
    protected $zohoCrmModule;
    protected $options;
    protected $data=array();
    protected $position;
    protected $range=null;
    protected $method;

    protected $batchSize = 200;

    public function __construct(ZohoCrmModule $zohoCrmModule, $options=array(), $method="getRecords") {
        $this->zohoCrmModule = $zohoCrmModule;
        $this->options = $options;
        $this->method = $method;

        if (array_key_exists('BatchSize', $this->options)) {
            $this->batchSize = $this->options['BatchSize'];
        }
    }

    protected function isInRange($position) {
        return ($this->range && $position >= $this->range['fromIndex'] && $position <= $this->range['toIndex']);
    }

	protected function getRecord($record) {
		$result = array();
		foreach ($record->FL as $field) {
			$result[$field->val] = $field->content;
		}
		$this->data[$this->range['fromIndex']+$record->no-1] = $result;
	}

    protected function getRange($position) {
        if ($this->isInRange($position)) return;
        $this->data = array();
        $this->range = array();
        $this->range['fromIndex'] = (floor(($position-1)/$this->batchSize)*$this->batchSize)+1;
        $this->range['toIndex'] = $this->range['fromIndex']+$this->batchSize-1;
        $options = $this->range+$this->options;
        $response = $this->zohoCrmModule->call($this->method, $options)->response;
        if (isset($response->result)) {
        	if (count($response->result->{$this->zohoCrmModule->name}->row) === 1) {
        		$this->getRecord($response->result->{$this->zohoCrmModule->name}->row);
        	} else {
				foreach ($response->result->{$this->zohoCrmModule->name}->row as $record) {
					$this->getRecord($record);
				}
			}
		}
    }

    public function rewind() {
        $this->position = 1;
    }

    function current() {
        return $this->data[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        if (!$this->isInRange($this->position)) {
            $this->getRange($this->position);
        }
        return array_key_exists($this->position, $this->data);
    }
}
