<?php 
use Bpez\Infuse\Util; 
use Bpez\Infuse\View; 
?>
<div class="infuseScaffold">

	<div class="page-header">
	  <h1><?php echo $header['name']; ?> <small> <?php echo $header['description']; ?></small></h1>
	</div>

	<?php  View::fuseAlerts($message); ?>

	<table class="table table-striped table-bordered">
			<?php foreach ($columns as $column): ?>
			<tr>

				<?php if (array_key_exists("select", $column)): ?>

						<?php 
						foreach ($column['select'] as $value):
								if ($entries->{$column['field']} == $value["id"]): 
									$columnName = end($value); ?>
									<th><?php echo Util::cleanName($column['field']); ?></th>
									<td><?php echo $columnName; ?></td>
								<?php
								endif; 
						endforeach; ?>
				<?php elseif (array_key_exists("upload", $column)): ?>
					<th><?php echo Util::cleanName($column['field']); ?></th>
					<td>
						<?php if ($entries->{$column['field']} != "" && preg_match('/(\.jpg|\.png|\.gif)$/', $entries->{$column['field']} )): ?>
							<img class="" src="<?php echo $entries->url($column['field']); ?>">
						<?php elseif ($entries->{$column['field']} != ""): ?>
							<a href="<?php echo $entries->url($column['field']); ?>"><?php echo $entries->{$column['field']}; ?></a>
						<?php endif; ?> 
					</td>

				<?php else: ?>

				<th><?php echo Util::cleanName($column['field']); ?></th>
				<td><?php echo $entries->{$column['field']}; ?></td>

				<?php endif; ?>
			</tr>
			<?php endforeach; ?>

			<tr>
				<td colspan="2"> 
					<?php if (Util::get("parent") && Util::get("pid")): ?>
					<div class="btn-group">
					    <a class="btn btn-small" href="<?php echo Util::redirectBackToParentUrl(Util::classToString($entries), Util::get("pid")); ?>">Back</a>
					    <a class="btn btn-small" href="?action=e&id=<?php echo $entries->id; ?>&pid=<?php echo Util::get("pid") ?>&parent=<?php echo Util::get("parent"); ?>">Edit</a>
							<a class="btn btn-small" href="?action=d&id=<?php echo $entries->id; ?>&pid=<?php echo Util::get("pid") ?>&parent=<?php echo Util::get("parent"); ?>" onclick="return confirm('Confirm delete?');">Delete</a>
					</div>
					<?php else: ?>
					<div class="btn-group">
					    <a class="btn btn-small" href="?action=l">List</a>
					    <a class="btn btn-small" href="?action=e&id=<?php echo $entries->id; ?>">Edit</a>
							<a class="btn btn-small" href="?action=d&id=<?php echo $entries->id; ?>" onclick="return confirm('Confirm delete?');">Delete</a>
					</div>
					<?php endif; ?>
				</td>
			</tr>

	</table>
</div>