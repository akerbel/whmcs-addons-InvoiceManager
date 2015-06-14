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
		"InvoicesPerPage" => array ("FriendlyName" => "Invoices per page", "Type" => "text", "Size" => "25", "Description" => "", "Default" => "20", ),
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
	if (isset($_POST['checkbox']) and (count($_POST['checkbox']))){
		$result = im_invoice_list::saveAll();
		echo $result['message'];
	}
	$list = new im_invoice_list($vars['InvoicesPerPage']);
	include_once('templates/list.php');
}