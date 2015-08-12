<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");
?>
<style>
	.blocked_invoice td {
		background-color: #f0fff0!important;
	}
</style>

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
				<th>actions</th>
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
				<th>active</th>
			</tr>
			<?php if (!count($list->invoices)) { ?>
				<tr><td colspan="<?php echo count($list->tablehead); ?>" align="center">No invoices</td></tr>
			<?php } ?>
			<?php foreach ($list->invoices as $invoice) {?>
				<tr <?php if ($invoice['blocked']){ ?> class="blocked_invoice" <?php } ?> >
					<td align="center">
						<input class="checkall" type="checkbox" id = "checkbox_<?=$invoice['id']?>" name="checkbox[<?=$invoice['id']?>]" <?php if ($invoice['blocked']) echo 'disabled title="Invoice blocked"'; ?>>
					</td>
					<td style="min-width: 76px;">
						<img style="cursor:pointer;" width="16" border="0" height="16" alt="Delete" src="images/delete.gif" class="delete_button<?php if ($invoice['blocked']) echo '_disabled'; ?>" id="delete_<?=$invoice['id']?>" title="<?php if ($invoice['blocked']){ echo 'Invoice blocked'; }else{ echo 'Delete';} ?>">
						<input class="delete_checkbox" type="checkbox" id="delete_checkbox_<?=$invoice['id']?>" name="delete_checkbox[<?=$invoice['id']?>]" style="display:none;">	
						<a style="cursor:pointer;" onclick="window.open('../viewinvoice.php?id=<?=$invoice['id']?>','windowfrm','menubar=yes,toolbar=yes,scrollbars=yes,resizable=yes,width=750,height=600')" title="Print version"><img src="/modules/addons/InvoiceManager/templates/print.png"></a>
						<a style="cursor:pointer;" onclick="window.open('../dl.php?type=i&id=<?=$invoice['id']?>&viewpdf=1','pdfinv','')" title="PDF"><img src="/modules/addons/InvoiceManager/templates/pdf.png"></a>
						<a style="cursor:pointer;" onclick="window.location='../dl.php?type=i&id=<?=$invoice['id']?>'" title="Download PDF"><img src="/modules/addons/InvoiceManager/templates/downloadpdf.png"></a>
					</td>
					<?php foreach ($invoice as $key=>$value) {?>
						<?php if (($key == 'invoicenum') or ($key == 'notes')){?>
							<td><input class="invoice_data im_<?=$key?>" type="text" value="<?=$value?>" name="invoices[<?=$invoice['id']?>][<?=$key?>]" invoice_id="<?=$invoice['id']?>" <?php if ($invoice['blocked']) echo 'disabled title="Invoice blocked"'; ?>></td>
						<?php }elseif ($key == 'status'){ ?>
							<td>
								<select class="invoice_data im_<?=$key?>" name="invoices[<?=$invoice['id']?>][<?=$key?>]" invoice_id="<?=$invoice['id']?>" <?php if ($invoice['blocked']) echo 'disabled title="Invoice blocked"'; ?>>
									<?php foreach ($list->statuses as $status){ ?>
										<option value="<?=$status?>"<?php if ($status == $value){ ?> selected<?php } ?>><?=$status?></option>
									<?php } ?>
								</select>
							</td>
						<?php }elseif (($key == 'items') or ($key == 'userid') or ($key == 'blocked')){ ?>

						<?php }elseif (($key == 'firstname') or ($key == 'lastname') or ($key == 'companyname')){ ?>
							<td><a href="clientssummary.php?userid=<?=$invoice['userid']?>"><?=$value?></a></td>
						<?php }elseif (($key == 'credit') or ($key == 'total') or ($key == 'companyname')){ ?>
							<td><a class="invtooltip" href="invoices.php?action=invtooltip&id=<?=$invoice['id']?>&userid=<?=$invoice['userid']?>&token=<?php echo generate_token("plain"); ?>"><?=$value?></a></td>
						<?php }elseif ($key == 'id'){ ?>
							<td><a href="invoices.php?action=edit&id=<?=$invoice['id']?>"><?=$value?></a></td>
						<?php }else{ ?>
							<td><?=$value?></td>
						<?php } ?>
					<?php } ?>
					<td align="center">
						<input class="active" type="checkbox" id="active_checkbox_<?=$invoice['id']?>" invoice_id="<?=$invoice['id']?>" name="active_checkbox[<?=$invoice['id']?>]" <?php if (!$invoice['blocked']){?>checked<?php } ?>>
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
	<div id="items_table" style="display: none; position: absolute; border: 3px lightgrey solid;"></div>
</div>
</form>
<form name="active_form" id="active_form" method="post" action="">
	<input type="hidden" name="active_id" id="active_id">
</form>
<div><?=$list->paginator?></div>

<script>
	$('document').ready(function(){
		$('.active').on('click', function(){
			$('#active_id').attr({'value': $(this).attr('invoice_id')});
			$('#active_form').submit();
		});
		
		$('.invtooltip').on('click', function(e){
			$.ajax({
				'url': $(this).attr('href'),
				'success': function(data){
					$('#items_table')
						.html(data)
						.css('left', (e.pageX-180)+'px')
						.css('top', e.pageY+'px')
						.fadeIn();
					$('<div id="close_items_table" style="position: absolute; right: 0; top: 0; cursor: pointer;"><b>X</b></div>').appendTo('#items_table');
				},
			});
			return false;
		});
		
		$('#close_items_table').on('click', function(){
			$('#items_table').fadeOut();
		});
		
		$('#contentarea').on('click', function(){$('#items_table').fadeOut();});
		
		$('#checkall0').on('click', function(){
			$('.checkall').attr({'checked': $(this).prop('checked')});
		});
		
		$('.invoice_data').on('change', function(){
			$('#checkbox_'+$(this).attr('invoice_id')).attr({'checked': true});
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