<?php use Infuse\Util; ?>

<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">

<?php echo Util::debug($columns); ?>

<div class="scaffold">
	<table class="table table-striped table-bordered table-hover">
			<form method="post">
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
						<input type="text" name="<?php echo $column['field']; ?>" value="<?php echo $entries->{$column['field']}; ?>">
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
					<a href="?action=l&id=<?php echo $entries->id; ?>">List</a>
					<a href="?action=s&id=<?php echo $entries->id; ?>">Show</a>
					<a href="?action=d&id=<?php echo $entries->id; ?>" onclick="return confirm('Confirm delete?');">Delete</a>
				</td>
				<td> 
					<input type="submit" value="submit" class="btn">
				</td>
			</tr>
			</form>
	</table>
</div>