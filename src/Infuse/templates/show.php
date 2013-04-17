<?php 
use Infuse\Util; 
use Infuse\View; 
?>
<div class="scaffold">

	<div class="navbar">
	  <div class="navbar-inner">
	    <a class="brand" href="#"><?php echo $header['name'] ; ?></a>
	  </div>
	</div>

	<?php  View::fuseAlerts($message); ?>

	<table class="table table-striped table-bordered table-hover ">
			<?php foreach ($columns as $column): ?>
			<tr>
				<th><?php echo Util::cleanName($column['field']); ?></th>
				<td><?php echo $entries->{$column['field']}; ?></td>
			</tr>
			<?php endforeach; ?>

			<tr>
				<td colspan="2">
					<div class="btn-group">
					    <a class="btn btn-small" href="?action=l">List</a>
					    <a class="btn btn-small" href="?action=e&id=<?php echo $entries->id; ?>">Edit</a>
							<a class="btn btn-small" href="?action=d&id=<?php echo $entries->id; ?>" onclick="return confirm('Confirm delete?');">Delete</a>
					</div>
				</td>
			</tr>

	</table>
</div>