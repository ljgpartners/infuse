<?php if (isset($header['edit']) && count($header['associations']) > 0): ?>

<tr>
	<?php foreach ($header['associations'] as $association): ?>
	<table class="table table-striped table-bordered">
		<tr>
			<td colspan="2"><a href="?action=cc&pid=<?php echo $entries->id; ?>">Create <?php echo get_class(current($association)); ?></a></td>
		</tr>
		<?php foreach ($entries->has_many(key($association)) as $key => $value): ?>
			# code...
		<?php endforeach; ?>

	</table>
	<?php endforeach; ?>
</tr>

<?php endif; ?>