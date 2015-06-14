<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");
?>
<div><?=$list->paginator?></div>
<form name="invoice_list" method="post" action="">
<div class="tablebg">
	<table id="sortabletbl0" class="datatable" width="100%" cellspacing="1" cellpadding="3" border="0">
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
				<tr>
					<td>
						<input class="checkall" type="checkbox" name="checkbox[<?=$invoice['id']?>]">
					</td>
					<?php foreach ($invoice as $key=>$value) {?>
						<td>
							<input type="text" value="<?=$value?>" name="invoices[<?=$invoice['id']?>][<?=$key?>]">
						</td>
					<?php } ?>
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
	});
</script>