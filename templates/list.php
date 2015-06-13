<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");
?>
<div><?=$list->paginator?></div>
<div class="tablebg">
	<table id="sortabletbl0" class="datatable" width="100%" cellspacing="1" cellpadding="3" border="0">
		<tbody>
			<tr>
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
					<?php foreach ($invoice as $key=>$value) {?>
						<td>
							<input type="text" value="<?=$value?>" name="<?=$key?>_<?=$invoice['id']?>">
						</td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div><?=$list->paginator?></div>