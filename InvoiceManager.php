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
		"FirstInvoicenum" => array ("FriendlyName" => "First invoicenum", "Type" => "text", "Size" => "25", "Description" => "", "Default" => "1", ),
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
	if (isset($_POST['checkbox']) and (count($_POST['checkbox']))){
		$result = im_invoice_list::saveAll();
		echo im_invoice_list::showMessage($result);
	}
	
	$list = new im_invoice_list($vars);
	if ((isset($_GET['action'])) and ($_GET['action'] == 'fillgaps')){
		$fillresult = $list->fillGaps();
		echo im_invoice_list::showMessage($fillresult);
		include_once('templates/fillgaps.php');
	}else{
		include_once('templates/list.php');
	}
}