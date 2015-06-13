<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function InvoiceManager_config() {
    $configarray = array(
    "name" => "Invoice Manager",
    "description" => "",
    "version" => "0.1",
    "author" => "Anton Kerbel",
    "language" => "english",
    "fields" => array(
		"InvoicesPerPage" => array ("FriendlyName" => "Invoices per page", "Type" => "text", "Size" => "25", "Description" => "", "Default" => "50", ),
		));
    return $configarray;
}

function InvoiceManager_activate() {
    return array('status'=>'success','description'=>'Success!');
}

function InvoiceManager_deactivate() {
    return array('status'=>'success','description'=>'Success!');
}

function InvoiceManager_output($vars) {
	include_once('model/im_invoice_list.php');
	include_once('model/im_invoice.php');
	$page = $_GET['page'];
	$list = new im_invoice_list($vars['InvoicesPerPage'],$page);
	include_once('templates/list.php');
}