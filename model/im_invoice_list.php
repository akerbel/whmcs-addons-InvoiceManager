<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");

class im_invoice_list {
	
	public $mainurl = '/whmcs_oss/admin/addonmodules.php?module=InvoiceManager';
	public $page = 1;
	public $perpage;
	public $maxpage;
	public $order = 'id';
	public $sort = 'DESC';
	public $invoices = array();
	public $tablehead;
	public $paginator;
	
	public function __construct($perpage){
		$this->perpage = $perpage;
	
		if ($_GET['page'] != NULL) $this->page = $_GET['page'];
		if ($_GET['order'] != NULL) $this->order = $_GET['order'];	
		if ($_GET['sort'] != NULL) $this->sort = $_GET['sort'];
		
		$this->createPaginator();
		$result = full_query("
			SELECT *
			FROM tblinvoices
			ORDER BY ".$this->order." ".$this->sort."
			LIMIT ".(($this->page-1)*$this->perpage).", $perpage
		");
		
		while ($invoice = mysql_fetch_assoc($result)){
			
			$this->invoices[] = $invoice;
		}
		$this->tablehead = array_keys($this->invoices[0]);
	}
	
	public function createPaginator(){
		$result = full_query("
			SELECT count(*) AS count
			FROM tblinvoices
		");
		$count = mysql_fetch_assoc($result);
		$this->maxpage = floor($count['count']/$this->perpage);
		if (ceil($count['count']/$this->perpage) != $this->maxpage) $this->maxpage += 1;
		$res = '';
		if ($this->page>1)
			$res .= '<a href="'.$this->getUrl(array('page' => ($this->page-1))).'"><< </a>';
		if ($this->page-4 > 1)
			$res .= '.. ';
		for ($i = $this->page-4; $i <= $this->page+4; $i++) {
			if (($i>0) and ($i<=$this->maxpage)){
				if ($i!= $this->page) 
					$res .= '<a href="'.$this->getUrl(array('page'=>$i)).'">'.$i.' </a>';
				else
					$res .= '<b>'.$i.' </b>';
			}
		}
		if ($this->page+4 < $this->maxpage)
			$res .= '.. ';
		if ($this->page<$this->maxpage)
			$res .= '<a href="'.$this->getUrl(array('page' => ($this->page+1))).'">>> </a>';
		
		$this->paginator = $res;
	}
	
	public function getUrl($newdata = array()){
		$data = array(
			'page' => $this->page,
			'order' => $this->order,
			'sort' => $this->sort,
		);
		foreach ($newdata as $k=>$v){
			$data[$k] = $v;
		}
		$url = $this->mainurl;
		foreach ($data as $k=>$v){
			$url .= '&'.$k.'='.$v;
		}
		return $url;
	}
	
	public function toggleSort($sort){
		if ($sort == 'DESC') return 'ASC';
		if ($sort == 'ASC') return 'DESC';
	}
	
	public static function save(){
		$checkboxes = $_POST['checkbox'];
		$invoices = $_POST['invoices'];
		foreach ($checkboxes as $id=>$value){
			if ($value == 'on'){
				$update = array();
				foreach ($invoices[$id] as $k=>$v){
					$update[$k] = $v;
				}
				update_query('tblinvoices', $update, array('id' => $id));
			}
		}
	}
}

?>