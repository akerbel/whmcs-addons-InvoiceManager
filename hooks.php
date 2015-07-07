<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function hook_invoice_manager_createinvoicenum($vars){
	/*$max = mysql_fetch_assoc(full_query('SELECT MAX(CAST(invoicenum AS INT)) AS max FROM tblinvoices'));
	if ($max == null) $invoicenum = 1;
	else $invoicenum = $max['max']+1;
	update_query('tblinvoices', array('invoicenum' => $invoicenum), array('id' => $vars['invoiceid']));*/
	
	$invoice = full_query('SELECT invoicenum FROM tblinvoices');
	$invoices = array();
	while ($inv = mysql_fetch_assoc($invoice)){
		if (($inv['invoicenum']) and ($inv['invoicenum'] != '')) $invoices[] = (int)$inv['invoicenum'];
	}
	sort($invoices);
	$max = array_pop($invoices);
	$newinvoicenum = (int)$max+1;
	update_query('tblinvoices', array('invoicenum' => $newinvoicenum), array('id' => $vars['invoiceid']));
}

add_hook('InvoicePaid', 555, 'hook_invoice_manager_createinvoicenum');