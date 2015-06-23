<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");
?>
<style> 
.im_id{
	width: 24px;
}
.im_userid{
	width: 30px;
}
.im_invoicenum{
	width: 60px;
}
.im_date, .im_duedate{
	width: 70px;
}
.im_datepaid{
	width: 120px;
}
.im_subtotal, .im_credit, .im_tax, .im_tax2, .im_total, .im_taxrate, .im_taxrate2{
	width: 60px;
}
.im_status{
	width: 90px;
}
.im_paymentmethod{
	width: 100px;
}
.im_notes{
	width: 60px;
}
</style>
<div><?=$list->paginator?></div>
<form name="invoice_list" method="post" action="">
<div class="tablebg">
	<table class="datatable" width="100%" cellspacing="1" cellpadding="3" border="0">
		<tbody>
			<tr>
				<th><input id="checkall0" type="checkbox"></th>
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
			<?php foreach ($list->invoices as $invoice) {?>
				<tr class="invoice_tr">
					<td>
						<input class="checkall" type="checkbox" id = "checkbox_<?=$invoice['id']?>" name="checkbox[<?=$invoice['id']?>]">
					</td>
					<?php foreach ($invoice as $key=>$value) {?>
						<td>
							<?php if (($key == 'id') or ($key == 'notes')){?>
								<input class="invoice_data im_<?=$key?>" type="text" value="<?=$value?>" name="invoices[<?=$invoice['id']?>][<?=$key?>]" invoice_id="<?=$invoice['id']?>">
							<?php }elseif ($key == 'status'){ ?>
								<select class="invoice_data im_<?=$key?>" name="invoices[<?=$invoice['id']?>][<?=$key?>]" invoice_id="<?=$invoice['id']?>">
									<?php foreach ($list->statuses as $status){ ?>
										<option value="<?=$status?>"<?php if ($status == $value){ ?> selected<?php } ?>><?=$status?></option>
									<?php } ?>
								</select>
							<?php }elseif ($key == 'items'){ ?>

							<?php }else{ ?>
								<?=$value?>
							<?php } ?>
						</td>
					<?php } ?>
				</tr>
				<tr style="display:none;">
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
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<input class="btn" type="submit" value="Save" name="Save">
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
			$(this).next('tr').toggle();
		});
	});
</script>