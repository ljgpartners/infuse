<?php 
use Infuse\Util; 
use Infuse\View; 
?>

<div class="infuseScaffold">

	<div class="navbar">
	  <div class="navbar-inner">
	    <a class="brand" href="#"><?php echo $header['name'] ; ?></a>
	  </div>
	</div>

	<?php  View::fuseAlerts($message); ?>

	<table class="table  table-bordered table-striped">
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
			<td>
				<?php if (array_key_exists("select", $column)):

								foreach ($column['select'] as $value):
										if ($entry->{$column['field']} == $value->id):
											$attributes = $value->attributes;
											end($attributes);
											$columnName = current($attributes);
											echo $columnName;
										endif; 
								endforeach;

							elseif ($column['type'] == "tinyint"): ?>
								<input type="checkbox" <?php echo ($entry->{$column['field']} == 1)? "checked='checked'" : ""; ?> 
									data-checked="<?php echo $entry->{$column['field']}; ?>" data-id="<?php echo $entry->id; ?>"
									data-url='<?php echo str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']); ?>' 
									name="<?php echo $column['field']; ?>" class="infuseBoolean">
							<?php
							else: 
								echo (($column['type'] == "text"))? Util::truncateText($entry->{$column['field']}, "25") : $entry->{$column['field']};
							endif; ?>
			</td>
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
	  	<?php 
	  	$pagination = $header['pagination']; 
	  	if ($pagination['active_page'] != 1): ?>
	  		<li><a href="?pg=<?php echo $pagination['active_page']-1; ?>">&laquo;</a></li>
	  	<?php else: ?>
	  		<li class="disabled"><a href="javascript: void(0)">&laquo;</a></li>
	  	<?php
	  	endif;
	  	
	  	if ($pagination['count'] > $pagination['limit']):  
	  		$times = ceil((int)$pagination['count']/(int)$pagination['limit']);
	  		for ($i=1; $i < $times+1; $i++): ?>
	  			<li class="<?php echo ($pagination['active_page'] == $i)? "active" : ""; ?>">
	  				<a href="?pg=<?php echo $i; ?>">
	  					<?php echo $i; ?>
	  				</a>
	  			</li>
	  	<?php endfor; ?>
	  	 <li><a href="?pg=a">View All</a></li>
	  	<?php endif; ?>
	  	
	  	<?php if ($pagination['active_page'] != $times): ?>
	    	<li><a href="?pg=<?php echo $pagination['active_page']+1; ?>">&raquo;</a></li>
	    <?php else: ?>
	    	<li class="disabled"><a href="javascript: void(0)">&raquo;</a></li>
	   	<?php endif; ?> 
	    
	  </ul>
	</div>
</div>


