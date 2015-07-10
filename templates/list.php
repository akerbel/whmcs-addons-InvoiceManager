<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");
?>
<div>
Show invoices: 
<?php foreach ($list->statuses as $status){ ?>
	<?php if ($list->status != $status) {?>	
		<a href="<?php echo $list->getUrl(array('status'=>$status)); ?>"><?=$status?></a>
	<?php }else{ ?>
		<b><?=$status?></b>
	<?php } ?>
<?php } ?>
</div>
<div>Total invoices: <b><?=$list->fullcount?></b></div>
<div>Total <?=$list->status?> invoices: <b><?=$list->count?></b></div>
<div><?=$list->paginator?></div>
<form name="invoice_list" method="post" action="">
<div class="tablebg">
	<table class="datatable" width="100%" cellspacing="1" cellpadding="3" border="0">
		<tbody>
			<tr>
				<th><input id="checkall0" type="checkbox"></th>
				<th><img width="16" border="0" height="16" alt="Delete" src="images/delete.gif"></th>
				<?php foreach ($list->tablehead as $value) {?>
					<th>
						<?php if ($list->order != $value){ ?>
							<a href="<?php echo $list->getUrl(array('order'=>$value)); ?>"><?=$value?></a>
						<?php }else{ ?>
							<a href="<?php echo $list->getUrl(array('order'=>$value, 'sort'=>$list->toggleSort($list->sort))); ?>">
								<?=$value?>
								<img class="absmiddle" src="images/<?php echo strtolower($list->sort);?>.gif">
							</a>
						<?php } ?>
					</th>
				<?php } ?>
			</tr>
			<?php if (!count($list->invoices)) { ?>
				<tr><td colspan="<?php echo count($list->tablehead); ?>" align="center">No invoices</td></tr>
			<?php } ?>
			<?php foreach ($list->invoices as $invoice) {?>
				<tr class="invoice_tr">
					<td>
						<input class="checkall" type="checkbox" id = "checkbox_<?=$invoice['id']?>" name="checkbox[<?=$invoice['id']?>]">
					</td>
					<td>
						<img style="cursor:pointer;" width="16" border="0" height="16" alt="Delete" src="images/delete.gif" class="delete_button" id="delete_<?=$invoice['id']?>">
						<input class="delete_checkbox" type="checkbox" id="delete_checkbox_<?=$invoice['id']?>" name="delete_checkbox[<?=$invoice['id']?>]" style="display:none;">
					</td>
					<?php foreach ($invoice as $key=>$value) {?>
						<?php if (($key == 'invoicenum') or ($key == 'notes')){?>
							<td><input class="invoice_data im_<?=$key?>" type="text" value="<?=$value?>" name="invoices[<?=$invoice['id']?>][<?=$key?>]" invoice_id="<?=$invoice['id']?>"></td>
						<?php }elseif ($key == 'status'){ ?>
							<td>
								<select class="invoice_data im_<?=$key?>" name="invoices[<?=$invoice['id']?>][<?=$key?>]" invoice_id="<?=$invoice['id']?>">
									<?php foreach ($list->statuses as $status){ ?>
										<option value="<?=$status?>"<?php if ($status == $value){ ?> selected<?php } ?>><?=$status?></option>
									<?php } ?>
								</select>
							</td>
						<?php }elseif (($key == 'items') or ($key == 'userid')){ ?>

						<?php }elseif (($key == 'firstname') or ($key == 'lastname') or ($key == 'companyname')){ ?>
							<td><a href="clientssummary.php?userid=<?=$invoice['userid']?>"><?=$value?></a></td>
						<?php }elseif (($key == 'credit') or ($key == 'total') or ($key == 'companyname')){ ?>
							<td><a target="" href="invoices.php?action=invtooltip&id=<?=$invoice['id']?>&userid=<?=$invoice['userid']?>&token=<?php echo generate_token("plain"); ?>"><?=$value?></a></td>
						<?php }elseif ($key == 'id'){ ?>
							<td><a href="invoices.php?action=edit&id=<?=$invoice['userid']?>"><?=$value?></a></td>
						<?php }else{ ?>
							<td><?=$value?></td>
						<?php } ?>
					<?php } ?>
				</tr>
				<tr style="display:none;" class="invoiceitems">
					<td colspan="<?php echo count($list->tablehead); ?>">
						<table width="100%">
							<tbody>
								<tr>
									<th>#</th><th>Description</th><th>Amount</th>
								</tr>
								<?php foreach ($invoice['items'] as $i=>$item){ ?>
									<tr>
										<td align="center">
											<?php echo $i+1; ?>
										</td>
										<td>
											<?=$item['description']?>
										</td>
										<td align="center">
											<?=$item['amount']?>
										</td>
									</tr>
								<?php } ?>
								<?php if (!count($invoice['items'])) { ?>
									<tr>
										<td align="center" colspan="3">No items</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	With selected:
	<input class="btn" type="submit" value="Save" name="Save" id="Save">
	<input class="btn" type="submit" value="Delete" name="Delete" id="Delete">
	<br>
	<input class="btn" type="button" value="Fill Gaps" name="fillgaps" id="fillgaps">
</div>
<div><?=$list->paginator?></div>

<script>
	$('document').ready(function(){
		$('#checkall0').on('click', function(){
			$('.checkall').attr({'checked': $(this).prop('checked')});
		});
		
		$('.invoice_data').on('change', function(){
			$('#checkbox_'+$(this).attr('invoice_id')).attr({'checked': true});
		});
		
		$('.invoice_tr').on('click', function(){
			$('.invoiceitems').hide();
			$(this).next('tr').toggle();
		});
		
		$('#Save').on('click', function(){
			if (confirm("Are you sure?")) {
				return true;
			}else{
				return false;
			}
		});
		
		$('#Delete').on('click', function(){
			if (confirm("Are you sure you want to delete this invoices?")) {
				$('.checkall').each(function(index){
					if ($(this).attr('checked')){
						$(this).attr({'checked': false});
						$('#delete_'+$(this).attr('id')).attr({'checked': true});
					}
				});
				return true;
			}else{
				return false;
			}
		});
		
		$('.delete_button').on('click', function(){
			if (confirm("Are you sure you want to delete this invoice?")) {
				$(this).next('.delete_checkbox').attr({'checked': true});
				$('.checkall').attr({'checked': false});
				$('form[name="invoice_list"]').submit();
			}
		});
		
		$('#fillgaps').on('click', function(){
			if (confirm("Are you sure you want to fill in the gaps? The action is irreversible.")) {
				document.location.href ='<?php echo $list->getUrl(array('action' => 'fillgaps')); ?>';
			}
		});
	});
</script>