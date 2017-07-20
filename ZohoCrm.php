<?php

include('ZohoCrmModule.php');

class ZohoCrm extends Zoho {

    protected $modules = array(
        'Leads' => null,
        'Contacts' => null,
        'Accounts' => null,
        'Products' => null,
        'Potentials' => null,
        'Notes' => null
    );

    public function __construct($authToken) {
        parent::__construct('crmapi', 'https://crm.zoho.com/crm/private', $authToken);
    }

    public function module($name) {
        if (array_key_exists($name, $this->modules)) {
            if ($this->modules[$name] === null) {
                $this->modules[$name] = new ZohoCrmModule($name, $this);
            }
            return $this->modules[$name];
        }
        throw new Exception('Unknown module: ' . $name);
    }
}

