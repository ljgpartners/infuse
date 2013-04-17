<?php 
use Infuse\Util; 
use Infuse\View; 
?>

<?php  Util::debug($entries); ?>

<div class="scaffold">

	<div class="navbar">
	  <div class="navbar-inner">
	    <a class="brand" href="#"><?php echo $header['name'] ; ?></a>
	  </div>
	</div>

	<?php  View::fuseAlerts($message); ?>

	<table class="table table-striped table-bordered table-hover">
			<form method="post" action="?">
				<input type="hidden" name="action" value="u">
			<?php if (Util::get("id")): ?>
				<input type="hidden" name="id" value="<?php  echo Util::get("id"); ?>">
			<?php endif; ?>

			<?php foreach ($columns as $column): ?>
			<tr>  
				<th><?php echo Util::cleanName($column['field']); ?></th>
				<td> 
				<?php switch ($column['type']):
						case 'varchar': ?>
						<input type="text" name="<?php echo $column['field']; ?>" value="<?php echo $entries->{$column['field']}; ?>">
				<?php 	break;
						case 'text': ?>
						<textarea name="<?php echo $column['field']; ?>"><?php echo $entries->{$column['field']}; ?></textarea>
				<?php 	break;
						case 'datetime': ?>
						<input type="text" class="selectedDateTime" name="<?php echo $column['field']; ?>" value="<?php echo $entries->{$column['field']}; ?>">
				<?php 	break;
						case 'int': ?>
						<input type="text" name="<?php echo $column['field']; ?>" pattern="\d+" value="<?php echo $entries->{$column['field']}; ?>"/>
				<?php 	break;
						case 'tinyint': ?> 
						<select name="<?php echo $column['field']; ?>">
							<option value="0" <?php echo ($entries->{$column['field']} == 0)? 'selected="selected"' : ""; ?> >No</option>
							<option value="1" <?php echo ($entries->{$column['field']} == 1)? 'selected="selected"' : ""; ?> >Yes</option>
						</select>
				<?php break;
						default: ?>
							<input type="text" name="<?php echo $column['field']; ?>" value="<?php echo $entries->{$column['field']}; ?>">
				<?php		
					endswitch;
				?>

				</td>
			</tr>
			<?php endforeach; ?>

			<tr>
				<td>
					<div class="btn-group">
				    <a class="btn btn-small" href="?action=l">List</a>
				  	<?php if (isset($header['edit'])): ?>
				  	<a class="btn btn-small" href="?action=s&id=<?php echo $entries->id; ?>">Show</a>
						<a class="btn btn-small" href="?action=d&id=<?php echo $entries->id; ?>" onclick="return confirm('Confirm delete?');">Delete</a>
						<?php endif; ?>
				  </div>
				</td>
				<td> 
					<input type="submit" value="submit" class="btn btn-small btn-success">
				</td>
			</tr>
			</form>
	</table>
</div>