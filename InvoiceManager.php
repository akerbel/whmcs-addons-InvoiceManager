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
		"NumberOfDigits" => array ("FriendlyName" => "Number of digits", "Type" => "text", "Size" => "25", "Description" => "The number of digits in the number of. If you don't want to use first zero, just type 0", "Default" => "6", ),
		));
    return $configarray;
}

function InvoiceManager_activate() {
	$result = mysql_fetch_assoc(full_query('Show tables like "mod_InvoiceManager"'));
	if (!$result){
		$query = "CREATE TABLE `mod_InvoiceManager` (`id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`invoiceid` INT(10) NOT NULL,
			`blocked` INT(1) NOT NULL
		) ENGINE=InnoDB CHARACTER SET=utf8";
		$result = full_query($query);
		if (!$result) return array('status'=>'error','description'=>'Can`t create table mod_InvoiceManager.');
	}
    return array('status'=>'success','description'=>'Success!');
}

function InvoiceManager_deactivate() {
    return array('status'=>'success','description'=>'Success!');
}

function InvoiceManager_output($vars) {
	include_once('model/im_invoice_list.php');
	
	if ((isset($_POST['active_id'])) and ($_POST['active_id'])){
		$result = im_invoice_list::toggleInvoice($_POST['active_id']);
		echo im_invoice_list::showMessage($result);
	}
	
	if (isset($_POST['checkbox']) and (count($_POST['checkbox']))){
		$result = im_invoice_list::saveAll();
		echo im_invoice_list::showMessage($result);
	}
	
	if (isset($_POST['delete_checkbox']) and (count($_POST['delete_checkbox']))){
		$result = im_invoice_list::deleteAll();
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