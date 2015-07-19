<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");

class im_invoice_list {
	
	public $page = 1;
	public $perpage;
	public $firstinvoicenum;
	public $maxpage;
	public $order = 'invoicenum';
	public $sort = 'DESC';
	public $action = 'list';
	public $status = 'Paid';
	public $invoices = array();
	public $tablehead;
	public $paginator;
	public $statuses = array('Paid', 'Unpaid', 'Cancelled', 'Refunded', 'Collections');
	
	public function __construct($vars){
		$this->perpage = $vars['InvoicesPerPage'];
		$this->firstinvoicenum = $vars['FirstInvoicenum'];
	
		if ($_GET['page'] != NULL) $this->page = $_GET['page'];
		if ($_GET['order'] != NULL) $this->order = $_GET['order'];	
		if ($_GET['sort'] != NULL) $this->sort = $_GET['sort'];
		if ($_GET['status'] != NULL) $this->status = $_GET['status'];
		
		$this->createPaginator();
		$result = full_query("
			SELECT i.id AS id, i.invoicenum AS invoicenum, c.firstname AS firstname, c.lastname AS lastname, c.companyname AS companyname, 
				c.email AS email, i.duedate AS duedate,
				i.datepaid AS datepaid, i.status AS status, i.credit AS credit, i.total AS total,
				i.paymentmethod AS paymentmethod, i.notes AS notes, i.userid AS userid, im.blocked AS blocked
			FROM tblinvoices AS i
			INNER JOIN tblclients AS c ON c.id = i.userid
			LEFT JOIN mod_InvoiceManager AS im ON im.invoiceid = i.id
			WHERE i.status = '".$this->status."'
			".//"ORDER BY ".($this->order == 'invoicenum' ? "CAST(".$this->order." AS INT) " : $this->order.' '). $this->sort.
			" ORDER BY ".$this->order." ".$this->sort.
			" LIMIT ".(($this->page-1)*$this->perpage).", ".$this->perpage."
		");
		
		while ($invoice = mysql_fetch_assoc($result)){
			if (isset($invoice['duedate'])){
				$date = DateTime::createFromFormat('Y-m-d', $invoice['duedate']);
				if ($date) {
					$invoice['duedate'] = $date->format('d-m-Y');
				}
			}
			if (isset($invoice['datepaid'])){
				$date = DateTime::createFromFormat('Y-m-d G:i:s', $invoice['datepaid']);
				if ($date) {
					$invoice['datepaid'] = $date->format('d-m-Y G:i:s');
				}
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
		$this->fullcount = $count['count'];
		
		$result = full_query("
			SELECT count(*) AS count
			FROM tblinvoices
			WHERE status = '".$this->status."'
		");
		$count = mysql_fetch_assoc($result);
		$this->count = $count['count'];
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
			'status' => $this->status,
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
	
	public static function invoicenumPad($invoicenum){
		$digits_data = mysql_fetch_assoc(select_query('tbladdonmodules', 'value', array('module' => 'InvoiceManager', 'setting' => 'NumberOfDigits')));
		if ($digits_data) $digits = (int)$digits_data['value'];
		else $digits = 0;
		$newinvoicenum_str = str_pad((string)$invoicenum, $digits, "0", STR_PAD_LEFT);
		return $newinvoicenum_str;
	}
	
	public static function saveAll(){
		$checkboxes = $_POST['checkbox'];
		$invoices = $_POST['invoices'];
		foreach ($checkboxes as $id=>$value){
			if ($value == 'on'){
				/*if ($id != $invoices[$id]['id']){
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
				}*/
				
				$update = array();
				foreach ($invoices[$id] as $k=>$v){
					if ($k == 'invoicenum'){
						if ($v == '') { 
							$result = false;
						}else{
							$v = im_invoice_list::invoicenumPad($v);
							$result = mysql_fetch_assoc(full_query('
								SELECT id
								FROM tblinvoices
								WHERE invoicenum = '.$v.' AND id != '.$id.'
							'));
						}
						if ($result){
							return array(
								'result' => 'error', 
								'message' => 'Invoicenum#'.$v.' already exist(ID#'.$result['id'].'). Can`t change invoicenum for ID#'.$id,
							);
						}
					}
					$update[$k] = $v;
				}
				update_query('tblinvoices', $update, array('id' => $id));
			}
		}
		return array('result' => 'success', 'message' => 'Changes have been saved');
	}
	
	public static function deleteAll(){
		$delete = $_POST['delete_checkbox'];
		foreach ($delete as $id=>$value){
			full_query('DELETE FROM tblinvoices WHERE id = "'.$id.'"');
			full_query('DELETE FROM tblinvoiceitems WHERE invoiceid = "'.$id.'"');
		}
		return array('result' => 'success', 'message' => 'Invoice(s) has been deleted');
	}
	
	public static function toggleInvoice($id){
		$result = mysql_fetch_assoc(full_query('SELECT id, blocked FROM mod_InvoiceManager WHERE invoiceid = '.$id.''));
		if ($result['id']){
			update_query('mod_InvoiceManager', array('blocked' => ($result['blocked'] ? 0 : 1)), array('id' => $result['id']));
		}else{
			insert_query('mod_InvoiceManager', array('blocked' => 1, 'invoiceid' => $id));
		}
		return array('result' => 'success', 'message' => ($result['blocked'] ? 'Invoice#'.$id.' unblocked' : 'Invoice#'.$id.' blocked'));
	}
	
	public function getInvoicenums(){
		$invoicenums = array();
		$result = full_query('
			SELECT id, invoicenum 
			FROM tblinvoices 
			WHERE status = "Paid"
			ORDER BY id ASC
		');
		$i = $this->firstinvoicenum;
		while ($invoicenum = mysql_fetch_array($result)){
			$invoicenums[im_invoice_list::invoicenumPad($i)] = array('id' => $invoicenum['id'], 'invoicenum' => $invoicenum['invoicenum']);
			$i++;
		}
		return $invoicenums;
	}
	
	public function fillGaps(){
		$changes = array();
		foreach ($this->getInvoicenums() as $key=>$value){
			if ((string)$key!==(string)$value['invoicenum']){
				update_query('tblinvoices', array('invoicenum' => $key), array('id' => $value['id']));
				$changes[$value['id']] = array($key, $value['invoicenum']);
			}
		}
		if (!count($changes)){
			$message = 'Nothing to fill';
		}else{
			$message = 'The gaps were filled';
		}
		return array('result' => 'success', 'message' => $message, 'changes' => $changes);
	}
	
}

?>