<?php use Bpez\Infuse\Util; ?>

<?php if (isset($header['edit']) && $header['hasOneAssociation'] != false): ?>
<tr>

	<?php
		$hasOneAssociation = $header['hasOneAssociation'];
		$model = key($hasOneAssociation);
		$hasOneAssociation = current($hasOneAssociation);
		$childTitle = $hasOneAssociation[0];
		$childColumns = $hasOneAssociation[1];
		$numColumns = count($childColumns)+1;
	?>
	<table class="table table-striped table-bordered">
		<tr>
			<td colspan="<?php echo $numColumns; ?>">
				<h4><?php echo $childTitle; ?></h4>
			</td>
		</tr>
		<tr>
			<?php foreach ($childColumns as $column): ?>
			<th><?php echo $column; ?></th>
			<?php endforeach; ?>
			<th>
				<?php if ($entries->hasOne(ucfirst($model))->count() == 0): ?>
					<a href="<?php echo Util::getPath()."/".$model; ?>?action=c&pid=<?php echo $entries->id; ?>&parent=<?php echo Util::classToString($entries); ?>&oneToOne=1">Create one</a>
				<?php endif; ?>
			</th>
		</tr>
				
		<?php foreach ($entries->hasOne(ucfirst($model))->get() as $key => $child): ?>
		<tr>
			<?php foreach ($childColumns as $column): ?>
			<td><?php echo $child->{$column}; ?></td>
			<?php endforeach; ?>
			<td>
				<a href="<?php echo Util::getPath()."/".$model; ?>?action=s&id=<?php echo $child->id; ?>&pid=<?php echo $entries->id; ?>&parent=<?php echo Util::classToString($entries); ?>">show</a>
				<a href="<?php echo Util::getPath()."/".$model; ?>?action=e&id=<?php echo $child->id; ?>&pid=<?php echo $entries->id; ?>&parent=<?php echo Util::classToString($entries); ?>">edit</a>
				<a href="<?php echo Util::getPath()."/".$model; ?>?action=d&id=<?php echo $child->id; ?>&pid=<?php echo $entries->id; ?>&parent=<?php echo Util::classToString($entries); ?>" onclick="return confirm('Confirm delete?');">delete</a>
			</td>
		</tr>
		<?php endforeach; ?>


	</table>



</tr>
<?php endif; ?>