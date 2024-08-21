<?php if(!empty($houseAWBNotesData)) { ?>
	<ul>
		<?php foreach ($houseAWBNotesData as $key => $value) { ?>
			<li><?php echo $value->notes; ?></li>
		<?php } ?>
	</ul>
<?php } else { ?>
	<?php echo "Notes Not Found." ?>
<?php } ?>