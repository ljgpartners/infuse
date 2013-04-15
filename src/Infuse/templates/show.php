<?php use Infuse\Util; ?>

<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">



<div class="scaffold">
	<table class="table table-striped table-bordered table-hover">
			<?php foreach ($columns as $column): ?>
			<tr>
				<th><?php echo Util::cleanName($column['field']); ?></th>
				<td><?php echo $entries->{$column['field']}; ?></td>
			</tr>
			<?php endforeach; ?>

			<tr>
				<td colspan="2">
					<a href="?action=l&id=<?php echo $entries->id; ?>">List</a>
					<a href="?action=e&id=<?php echo $entries->id; ?>">Edit</a>
					<a href="?action=d&id=<?php echo $entries->id; ?>" onclick="return confirm('Confirm delete?');">Delete</a>
				</td>
			</tr>

	</table>
</div>