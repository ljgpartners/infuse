<?php use Infuse\Util; ?>

<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">

<div class="scaffold">
	<table class="table table-striped table-bordered table-hover">
		<tr>
			<td colspan="<?php echo count($columns)+1; ?>">
				<a href="?action=c">Create</a>
			  <div style="float:right;">
			 	<a href="?pg=a">View All</a>
			 	<?php foreach ($header['pagination'] as $key => $value): ?>
			 			<a href="?pg=">1</a>
			 	<?php endforeach; ?>
			  </div>
			</td>
		</tr>
		<tr>
			<?php foreach ($columns as $column): ?>
			<th><?php echo Util::cleanName($column['field']); ?></th>
			<?php endforeach; ?>
			<th></th>
		</tr>

		<?php foreach ($entries as $entry): ?>
		<tr>
			<?php foreach ($columns as $column): ?>
			<td><?php echo Util::truncateText($entry->{$column['field']}, "25"); ?></td>
			<?php endforeach; ?>
			<td>
				<a href="?action=s&id=<?php echo $entry->id; ?>">Show</a>
				<a href="?action=e&id=<?php echo $entry->id; ?>">Edit</a>
				<a href="?action=d&id=<?php echo $entry->id; ?>" onclick="return confirm('Confirm delete?');">Delete</a>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>


