<?php
if (!defined("WHMCS")) 
	die("This file cannot be accessed directly");
?>

<a href="<?php echo $list->getUrl(array('action'=>'list'));?>"> back </a>
<div class="tablebg">
	<table class="datatable" width="100%" cellspacing="1" cellpadding="3" border="0">
		<tbody>
			<tr>
				<th>Old ID</th><th>New ID</th>
			</tr>
			<?php foreach ($fillresult['changes'] as $old=>$new){ ?>
				<tr>
					<td align="right"><?=$old?></td><td align="left"><?=$new?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<a href="<?php echo $list->getUrl(array('action'=>'list'));?>"> back </a>