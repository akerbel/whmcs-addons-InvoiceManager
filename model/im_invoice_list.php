<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");

class im_invoice_list {
	
	public $page = 1;
	public $perpage;
	public $maxpage;
	public $order = 'id';
	public $sort = 'DESC';
	public $action = 'list';
	public $status = 'Paid';
	public $invoices = array();
	public $tablehead;
	public $paginator;
	public $statuses = array('Paid', 'Unpaid', 'Cancelled', 'Refunded', 'Collections');
	
	public function __construct($perpage){
		$this->perpage = $perpage;
	
		if ($_GET['page'] != NULL) $this->page = $_GET['page'];
		if ($_GET['order'] != NULL) $this->order = $_GET['order'];	
		if ($_GET['sort'] != NULL) $this->sort = $_GET['sort'];
		if ($_GET['status'] != NULL) $this->status = $_GET['status'];
		
		$this->createPaginator();
		$result = full_query("
			SELECT i.id AS id, i.invoicenum AS invoicenum, c.firstname AS firstname, c.lastname AS lastname, c.companyname AS companyname, 
				c.email AS email, i.date AS date, i.duedate AS duedate,
				i.datepaid AS datepaid, i.status AS status, i.paymentmethod AS paymentmethod, i.notes AS notes, i.userid AS userid
			FROM tblinvoices AS i
			INNER JOIN tblclients AS c ON c.id = i.userid
			WHERE i.status = '".$this->status."'
			ORDER BY ".$this->order." ".$this->sort."
			LIMIT ".(($this->page-1)*$this->perpage).", $perpage
		");
		
		while ($invoice = mysql_fetch_assoc($result)){
			$invoice['items'] = array();
			$items = full_query("
				SELECT description, amount
				FROM tblinvoiceitems
				WHERE invoiceid = ".$invoice['id']."
			");
			while ($item = mysql_fetch_assoc($items)){
				$invoice['items'][] = $item;
			}
			$this->invoices[] = $invoice;
		}
		$this->tablehead = array_keys($this->invoices[0]);
		array_pop($this->tablehead);array_pop($this->tablehead);
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
	
	public static function showMessage($result){
		if (is_array($result))
			return '<div class="'.$result['result'].'box"><span class="title">'.$result['result'].'</span><br>'.$result['message'].'</div>';
		else 
			return '<div class="infobox"><span class="title">'.$result.'</span></div>';
	}
	
	public function getUrl($newdata = array()){
		global $customadminpath;
		$data = array(
			'page' => $this->page,
			'order' => $this->order,
			'sort' => $this->sort,
			'action' => $this->action,
		);
		foreach ($newdata as $k=>$v){
			$data[$k] = $v;
		}
		$url = '/'.$customadminpath.'/addonmodules.php?module=InvoiceManager';
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
		return array('result' => 'success', 'message' => 'Changes have been saved');
	}
	
	public function getInvoicenums(){
		$invoicenums = array();
		$result = full_query('
			SELECT invoicenum 
			FROM tblinvoices 
			WHERE status = "Paid"
			ORDER BY invoicenum ASC
		');
		$i = 1;
		while ($invoicenum = mysql_fetch_array($result)){
			$invoicenums[$i] = $invoicenum['invoicenum'];
			$i++;
		}
		return $invoicenums;
	}
	
	public function fillGaps(){
		$changes = array();
		foreach ($this->getInvoicenums() as $key=>$value){
			if ($key!=$value){
				$result = $this->saveId($value, $key);
				if ($result['result']!='success') {
					$result['changes'] = $changes;
					return $result;
				}
				$changes[$value] = $key;
			}
		}
		if (!count($changes)){
			$message = 'Nothing to fill';
		}else{
			$message = 'The gaps were filled';
		}
		return array('result' => 'success', 'message' => $message, 'changes' => $changes);
	}
	
	private function saveId($id, $newid){
		$result = select_query('tblinvoices', 'id', array('id' => $newid));
		$data = mysql_fetch_array($result);
		if ($data){ 
			return array(
				'result' => 'error', 
				'message' => 'Invoice#'.$newid.' already exist. Can`t change invoice id from '.$id.' to '.$newid
			);
		}else{
			update_query('tblinvoices', array('id' => $newid), array('id' => $id));
			update_query('tblinvoiceitems', array('invoiceid' => $newid), array('invoiceid' => $id));
			update_query('tblorders', array('invoiceid' => $newid), array('invoiceid' => $id));
			$max = mysql_fetch_assoc(select_query('tblinvoices', 'max(id) AS max', array()));
			full_query('ALTER TABLE tblinvoices AUTO_INCREMENT = '.$max['max']);
			return array(
				'result' => 'success', 
				'message' => 'success!',
			);
		}
	}
}

?>