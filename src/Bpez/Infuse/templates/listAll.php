<?php 
use Bpez\Infuse\Util; 
use Bpez\Infuse\View; 
?>

<div class="infuseScaffold">

	<div class="page-header">
	  <h1><?php echo $header['name']; ?> <small> <?php echo $header['description']; ?></small></h1>
	</div>

	<?php  View::fuseAlerts($message); ?>

	<table class="table  table-bordered table-striped">
		<tr>
			<td colspan="<?php echo count($columns)+1; ?>">
				<a class="btn btn-small btn-success" href="?action=c">Create <?php echo $header['name']; ?></a>
			</td>
		</tr>
		<tr>
			<?php foreach ($columns as $column): ?>
				<?php if (in_array($column['field'], $header['list'])): ?>
					<th><?php echo Util::cleanName($column['field']); ?></th>
				<?php endif; ?>
			<?php endforeach; ?>
			<th></th>
		</tr>

		<?php foreach ($entries as $entry): ?>
		<tr>
			<?php foreach ($columns as $column): ?>
				<?php if (in_array($column['field'], $header['list'])): ?>
					<td>
					<?php if (array_key_exists("select", $column)):

									foreach ($column['select'] as $value):
											if ($entry->{$column['field']} == $value["id"]):
												$columnName = end($value);
												echo $columnName;
											endif; 
									endforeach;

								elseif ($column['type'] == "tinyint"): ?>
									<input type="checkbox" <?php echo ($entry->{$column['field']} == 1)? "checked='checked'" : ""; ?> 
										data-checked="<?php echo $entry->{$column['field']}; ?>" data-id="<?php echo $entry->id; ?>"
										data-url='<?php echo str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']); ?>' 
										name="<?php echo $column['field']; ?>" class="infuseBoolean">
					<?php elseif (array_key_exists("display_order", $column)): ?>
										<span><?php echo $entry->{$column['field']}; ?></span> <span class="icon-arrow-up"></span> <span class="icon-arrow-down"></span>
					<?php	else: 
									echo (($column['type'] == "text"))? Util::truncateText($entry->{$column['field']}, "25") : $entry->{$column['field']};
								endif; ?>
					</td>
				<?php endif; ?>
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
						<li><a href="?action=cd&id=<?php echo $entry->id; ?>">Duplicate</a></li>
				  </ul>
				</div>
				
			</td>
		</tr>
		<?php endforeach; ?>

		<?php if ($header['pagination']['count'] < 1): ?>
		<tr>
			<td colspan="<?php echo count($columns)+1; ?>">
				<div class="hero-unit">
				  <h1><?php echo $header['name']; ?> listing is empty.</h1>
				  <p>To create the first one click the create button below.</p>
				  <p>
				    <a href="?action=c" class="btn btn-success btn">
				      Create <?php echo $header['name']; ?>
				    </a>
				  </p>
				</div>
			</td>
		</tr>
	<?php endif; ?>
	</table>

	<?php if ($header['pagination']['count'] > 0): ?>
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
	  	
	  	<?php if (isset($times) && $pagination['active_page'] != $times): ?>
	    	<li><a href="?pg=<?php echo $pagination['active_page']+1; ?>">&raquo;</a></li>
	    <?php else: ?>
	    	<li class="disabled"><a href="javascript: void(0)">&raquo;</a></li>
	   	<?php endif; ?> 
	    
	  </ul>
	</div>
	<?php endif; ?>


</div>



