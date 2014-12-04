<?php

include('ZohoCrmModule.php');

// Enum
abstract class ZohoCrmUserType {
    const All      = 'AllUsers';
    const Active   = 'ActiveUsers';
    const Inactive = 'DeactiveUsers';
    const Admin    = 'AdminUsers';
    const ActiveConfirmedAdmin = 'ActiveConfirmedAdmins';
}

class ZohoCrm extends Zoho {

    protected $modules = array(
        'Accounts' => null,
        'Contacts' => null,
        'Leads'    => null,
        'Notes'    => null,
        'Products' => null,
        'Users'    => null
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

    public function __get($name) {
        return $this->module($name);
    }
}

