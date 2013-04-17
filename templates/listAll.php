<?php 
use Bpez\Infuse\Util; 
use Bpez\Infuse\View; 
?>

<div class="scaffold">

	<div class="navbar">
	  <div class="navbar-inner">
	    <a class="brand" href="#"><?php echo $header['name'] ; ?></a>
	  </div>
	</div>

	<?php  View::fuseAlerts($message); ?>

	<table class="table table-striped table-bordered table-hover table table-condensed">
		<tr>
			<td colspan="<?php echo count($columns)+1; ?>">
				<a class="btn btn-small btn-success" href="?action=c">Create</a>
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
				<div class="btn-group">
				  <a class="btn btn-small dropdown-toggle" data-toggle="dropdown" href="#">
				    Action
				    <span class="caret"></span>
				  </a>
				  <ul class="dropdown-menu">
				    <li><a href="?action=s&id=<?php echo $entry->id; ?>">Show</a></li>
						<li><a href="?action=e&id=<?php echo $entry->id; ?>">Edit</a></li>
						<li><a href="?action=d&id=<?php echo $entry->id; ?>" onclick="return confirm('Confirm delete?');">Delete</a></li>
				  </ul>
				</div>
				
			</td>
		</tr>
		<?php endforeach; ?>
	</table>

	<div class="pagination pagination-small pagination-centered">
	  <ul>
	  	<?php foreach ($header['pagination'] as $key => $value): ?>
			 			<a href="?pg=">1</a>
			 <?php endforeach; ?>
	  	<li class="disabled"><span>&laquo;</span></li>
	    <li class="active"><a href="#">1</a></li>
	    <li><a href="#">2</a></li>
	    <li><a href="#">3</a></li>
	    <li><a href="#">4</a></li>
	    <li><a href="#">5</a></li>
	    <li><a href=""><span>&raquo;</span></a></li>
	    <li><a href="?pg=a">View All</a></li>
	    
	  </ul>
	</div>
</div>


