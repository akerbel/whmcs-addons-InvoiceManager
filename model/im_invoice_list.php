<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");

class im_invoice_list {
	
	public $page;
	public $perpage;
	public $maxpage;
	public $invoices = array();
	public $tablehead;
	public $paginator;
	
	public function __construct($perpage, $page){
		$this->perpage = $perpage;
		if ($page != NULL)
			$this->page = $page;
		else 
			$this->page = 1;
		$this->create_paginator();
		$result = full_query("
			SELECT *
			FROM tblinvoices
			ORDER BY id DESC
			LIMIT ".($this->page-1).", $perpage
		");
		
		while ($invoice = mysql_fetch_assoc($result)){
			$this->invoices[] = $invoice;
		}
		$this->tablehead = array_keys($this->invoices[0]);
	}
	
	public function create_paginator(){
		$result = full_query("
			SELECT count(*) AS count
			FROM tblinvoices
		");
		$count = mysql_fetch_assoc($result);
		$this->maxpage = floor($count['count']/$this->perpage);
		
		$res = '';
		if ($this->page>1)
			$res .= '<a href="/admin/addonmodules.php?module=InvoiceManager&page='.($this->page-1).'"><< </a>';
		if ($this->page-4 > 1)
			$res .= '.. ';
		for ($i = $this->page-4; $i <= $this->page+4; $i++) {
			if (($i>0) and ($i<=$this->maxpage)){
				if ($i!= $this->page) 
					$res .= '<a href="/admin/addonmodules.php?module=InvoiceManager&page='.$i.'">'.$i.' </a>';
				else
					$res .= '<b>'.$i.' </b>';
			}
		}
		if ($this->page+4 < $this->maxpage)
			$res .= '.. ';
		if ($page<$this->maxpage)
			$res .= '<a href="/admin/addonmodules.php?module=InvoiceManager&page='.($this->page+1).'">>> </a>';
		
		$this->paginator = $res;
	}
	
	
}

?>