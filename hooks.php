<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function hook_InvoiceManager_InvoiceCreation($vars){
	logModuleCall('test', 'InvoiceCreation', $vars, '');
}

add_hook('InvoiceCreation', 1, 'hook_InvoiceManager_InvoiceCreation');