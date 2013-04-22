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
	<?php $errors = Util::flashArray("errors"); ?>
	<?php $fileErrors = Util::flashArray("file_errors"); ?>

	<?php  View::fuseAlerts($message); ?>

	<table class="table table-striped table-bordered">
			<form method="post" action="?" enctype="multipart/form-data">
				<input type="hidden" name="action" value="u">
			<?php if (Util::get("id")): ?>
				<input type="hidden" name="id" value="<?php  echo Util::get("id"); ?>">
			<?php endif; ?>

			<?php foreach ($columns as $column): ?>
			<tr>  
				<th><?php echo Util::cleanName($column['field']); ?></th>
				<td> 
				<?php if (array_key_exists("select", $column)): ?>

					<select name="<?php echo $column['field']; ?>">
						<?php 
						foreach ($column['select'] as $value):
								$attributes = $value->attributes;
								end($attributes);
								$columnName = current($attributes); 
								if ($entries->{$column['field']} == $value->id): ?>
									<option value="<?php echo $value->id; ?>" selected="selected"><?php echo $columnName; ?></option>
								<?php	
								else: ?>
									<option value="<?php echo $value->id; ?>"><?php echo $columnName;  ?></option>
								<?php
								endif; 
						endforeach; ?>
					</select>

				<?php elseif (array_key_exists("upload", $column)): ?>

					<input type="file" name="<?php echo $column['field']; ?>" >

					<?php if ($entries->{$column['field']} != ""): ?>
						</br>
						<button type="button" class="btn btn-mini btn-link" data-toggle="modal" data-target="#<?php echo "Modal".$column['field'].$entries->id; ?>">
							View current
						</button>

						<div id="<?php echo "Modal".$column['field'].$entries->id; ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						  <div class="modal-header">
						    <h3 id="myModalLabel"><?php echo $entries->{$column['field']}; ?></h3>
						  </div>
						  <div class="modal-body">
						    <img src="<?php echo $entries->url($column['field']); ?>">
						  </div>
						</div>
					<?php endif; ?> 

					<?php foreach ($column['upload']['validations'] as $val): ?>
						<?php if ($fileErrors && array_key_exists($column['field'], $fileErrors) && $val[1] == $fileErrors["{$column['field']}"]): ?>
							<?php unset($fileErrors["{$column['field']}"]); ?>
							</br><span class="label label-important"><?php echo $val[1]; ?></span>
						<?php else: ?>
						</br><span class="label label-info"><?php echo $val[1]; ?></span>
						<?php endif; ?>
					<?php	endforeach; ?>

				<?php else: ?>

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

				<?php endif; ?>
				
				<?php 
					if ($errors && isset($errors->messages["{$column['field']}"])): 
						foreach ($errors->messages["{$column['field']}"] as $err): ?>
							</br><span class="label label-important"><?php echo $err; ?></span>
						<?php 
						endforeach;
					endif; 

				?>

				<?php 
				if ($fileErrors && array_key_exists($column['field'], $fileErrors) && count($fileErrors) > 0): 
					foreach ($fileErrors as $err): ?>
							</br><span class="label label-important"><?php echo $err; ?></span>
					<?php 
					endforeach;
				endif; ?>

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

			<tr>
			<?php if (count($header['associations']) > 0): ?>
				<?php foreach ($header['associations'] as $association) ?>
				<table>
					<tr>
						<td colspan="2"><a href="?action=cc&pid=<?php echo $entries->id; ?>">Create <?php echo get_class($entries); ?></a><?php echo ; ?></td>
					</tr>
					<?php foreach ($entries->has_many($association) as $key => $value): ?>
						# code...
					<?php endforeach; ?>

				</table>
				<?php endforeach; ?>
			<?php endif; ?>
			</tr>

			</form>
	</table>
</div>