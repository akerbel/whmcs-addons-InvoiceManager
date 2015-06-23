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
			SELECT i.id AS id, c.firstname AS firstname, c.lastname AS lastname, c.companyname AS companyname, 
				c.email AS email, i.invoicenum AS invoicenum, i.date AS date, i.duedate AS duedate,
				i.datepaid AS datepaid, i.status AS status, i.paymentmethod AS paymentmethod, i.notes AS notes
			FROM tblinvoices AS i
			INNER JOIN tblclients AS c ON c.id = i.userid
			ORDER BY ".$this->order." ".$this->sort."
			LIMIT ".(($this->page-1)*$this->perpage).", $perpage
		");
		
		while ($invoice = mysql_fetch_assoc($result)){
			$this->invoices[] = $invoice;
		}
		$this->tablehead = array_keys($this->invoices[0]);
		$this->statuses = array('Unpaid', 'Paid', 'Cancelled', 'Refunded', 'Collections');
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
	
	public static function saveAll(){
		$checkboxes = $_POST['checkbox'];
		$invoices = $_POST['invoices'];
		foreach ($checkboxes as $id=>$value){
			if ($value == 'on'){
				if ($id != $invoices[$id]['id']){
					$result = select_query('tblinvoices', 'id', array('id' => $invoices[$id]['id']));
					$data = mysql_fetch_array($result);
					if ($data){ 
						return array(
							'result' => 'error', 
							'message' => 'Invoice#'.$invoices[$id]['id'].' already exist. Can`t change invoice id from '.$id.' to '.$invoices[$id]['id']
						);
					}else{
						update_query('tblinvoiceitems', array('invoiceid'=>$invoices[$id]['id']), array('invoiceid' => $id));
						update_query('tblorders', array('invoiceid'=>$invoices[$id]['id']), array('invoiceid' => $id));
						$max = mysql_fetch_assoc(select_query('tblinvoices', 'max(id) AS max', array()));
						full_query('ALTER TABLE tblinvoices AUTO_INCREMENT = '.$max['max']);
					}
				}
				
				$update = array();
				foreach ($invoices[$id] as $k=>$v){
					$update[$k] = $v;
				}
				update_query('tblinvoices', $update, array('id' => $id));
			}
		}
		return array('result' => 'success', 'message' => 'success!');
	}
}

?>