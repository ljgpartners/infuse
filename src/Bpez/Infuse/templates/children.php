<?php use Bpez\Infuse\Util; ?>

<?php if (isset($header['edit']) && count($header['associations']) > 0): ?>
<tr>

	<?php foreach ($header['associations'] as $association): 
					$model = key($association);
					$association = current($association);
					$childTitle = $association[0];
					$childColumns = $association[1];
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
			<th><?php echo Util::cleanName($column); ?></th>
			<?php endforeach; ?>
			<th><a href="<?php echo Util::getPath()."/".$model; ?>?action=c&pid=<?php echo $entries->id; ?>&parent=<?php echo Util::classToString($entries); ?>">Create </a></th>
		</tr>
				
		<?php foreach ($entries->hasMany(ucfirst($model))->get() as $key => $child): ?>
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
	<?php endforeach; ?>


</tr>
<?php endif; ?>