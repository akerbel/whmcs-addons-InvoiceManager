<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");
?>

<a href="<?php echo $list->getUrl(array('action'=>'list'));?>"> back </a>
<div class="tablebg">
	<table class="datatable" width="100%" cellspacing="1" cellpadding="3" border="0">
		<tbody>
			<tr>
				<th>id</th><th>Old ID</th><th>New ID</th>
			</tr>
			<?php foreach ($fillresult['changes'] as $id=>$invoicenum){ ?>
				<tr>
					<td align="center"><?=$id?></td>
					<td align="right"><?=$invoicenum[1]?></td>
					<td align="left"><?=$invoicenum[0]?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<a href="<?php echo $list->getUrl(array('action'=>'list'));?>"> back </a>