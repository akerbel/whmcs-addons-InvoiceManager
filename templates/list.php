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
						<?=$value?>
					</th>
				<?php } ?>
			</tr>
			<?php foreach ($list->invoices as $invoice) {?>
				<tr>
					<?php foreach ($invoice as $value) {?>
						<td>
							<?=$value?>
						</td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div><?=$list->paginator?></div>