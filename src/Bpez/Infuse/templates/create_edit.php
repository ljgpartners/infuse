<?php 
use Bpez\Infuse\Util; 
use Bpez\Infuse\View; 
?>


<div class="infuseScaffold">

	<div class="page-header">
	  <h1><?php echo $header['name']; ?> <small> <?php echo $header['description']; ?></small></h1>
	</div>
	
	<?php $errors = Util::flashArray("errors"); ?>
	<?php $fileErrors = Util::flashArray("file_errors"); ?>

	<?php  View::fuseAlerts($message); ?>

	<table class="table table-striped table-bordered">
			<form method="post" action="?<?php echo (Util::get("parent") && Util::get("pid"))? "parent=".Util::get("parent")."&pid=".Util::get("pid") : ""; ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="u">
			<?php if (Util::get("id") && Util::get("action") != "cd"): ?>
				<input type="hidden" name="id" value="<?php  echo Util::get("id"); ?>">
			<?php endif; ?>

			<?php if (Util::get("pid") && Util::get("parent")): ?>
				<input type="hidden" name="<?php echo Util::foreignKeyString(Util::get("parent")); ?>" value="<?php  echo Util::get("pid"); ?>">
				<?php if (Util::get("oneToOne")): ?>
					<input type="hidden" name="oneToOne" value="<?php echo Util::get("parent"); ?>">
				<?php endif; ?>
			<?php endif; ?>

			<?php foreach ($columns as $column): ?>
			<?php if ($column['field'] != "created_at" && $column['field'] != "updated_at" && !Util::isForeignKey($column['field']) ): ?>
			<tr>  
				<th><?php echo Util::cleanName($column['field']); ?></th>
				<td> 

				<?php if ($column['field'] == Util::getForeignKeyString($entries)): ?>
				
				<?php elseif (array_key_exists("ckeditor", $column)): ?>
					<textarea class="ckeditor" name="<?php echo $column['field']; ?>"><?php echo $entries->{$column['field']}; ?></textarea>

				<?php elseif (array_key_exists("select", $column)): ?>

					<select name="<?php echo $column['field']; ?>">
						<?php 
						if (array_key_exists("select_blank", $column)): ?>
							<option value=""></option>
						<?php 
						endif;
						foreach ($column['select'] as $value):
								$columnName = end($value);
								if ($entries->{$column['field']} == $value["id"]): ?>
									<option value="<?php echo $value["id"]; ?>" selected="selected"><?php echo $columnName; ?></option>
								<?php	
								else: ?>
									<option value="<?php echo $value["id"]; ?>"><?php echo $columnName;  ?></option>
								<?php
								endif; 
						endforeach; ?>
					</select>

				<?php elseif (array_key_exists("upload", $column)): ?>

					<input type="file" name="<?php echo $column['field']; ?>" >

					<?php if (property_exists($entries, $column['field']) && $entries->{$column['field']} != ""): ?>
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

				<?php elseif ($infuseLogin && ($column['field'] == "password" || $column['field'] == "password_confirmation" ) ): ?>
					<input type="password" name="<?php echo $column['field']; ?>" value="">

				<?php elseif ($infuseLogin && ($column['field'] == "last_login_date" || $column['field'] == "last_login_ip" || $column['field'] == "logins" ) ): ?>
					<input type="text" disabled="disabled" name="<?php echo $column['field']; ?>" value="<?php echo (property_exists($entries, $column['field']))? $entries->{$column['field']} : ""; ?>">
				
				<?php else: ?>

					<?php switch ($column['type']):
							case 'varchar': ?>
							<input type="text" name="<?php echo $column['field']; ?>" value="<?php echo $entries->{$column['field']}; ?>">
					<?php 	break;
							case 'text': ?>
							<textarea name="<?php echo $column['field']; ?>"><?php echo $entries->{$column['field']}; ?></textarea>
					<?php 	break;
							case 'datetime':
							case 'timestamp': ?>
							<input type="text" class="selectedDateTime" name="<?php echo $column['field']; ?>" value="<?php echo $entries->{$column['field']}; ?>">
					<?php 	break; 
							case 'date': ?>
							<input type="text" class="selectedDate" name="<?php echo $column['field']; ?>" value="<?php echo $entries->{$column['field']}; ?>">
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

				
				<?php if (array_key_exists("description", $column)): ?>
					</br><span class="label label-info"><?php echo $column['description'];  ?></span>
				<?php endif; ?>
				
				<?php 
					if ($errors && $errors->has("{$column['field']}")): 
						foreach ($errors->get("{$column['field']}") as $err): ?>
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
			<?php endif; ?>
			<?php endforeach; ?>

			<tr>
				<td>
					<?php if (Util::get("parent") && Util::get("pid")): ?>
					<div class="btn-group">
				    <a class="btn btn-small" href="<?php echo Util::redirectBackToParentUrl(Util::classToString($entries), Util::get("pid")); ?>">Back</a>
				  	<?php if (isset($header['edit'])): ?>
				  	<a class="btn btn-small" href="?action=s&id=<?php echo $entries->id; ?>&pid=<?php echo Util::get("pid"); ?>&parent=<?php echo Util::get("parent"); ?>">Show</a>
						<a class="btn btn-small" href="?action=d&id=<?php echo $entries->id; ?>&pid=<?php echo Util::get("pid"); ?>&parent=<?php echo Util::get("parent"); ?>" onclick="return confirm('Confirm delete?');">Delete</a>
						<?php endif; ?>
				  </div>
					<?php else: ?>
					<div class="btn-group">
				    <a class="btn btn-small" href="?action=l">List</a>
				  	<?php if (isset($header['edit'])): ?>
				  	<a class="btn btn-small" href="?action=s&id=<?php echo $entries->id; ?>">Show</a>
						<a class="btn btn-small" href="?action=d&id=<?php echo $entries->id; ?>" onclick="return confirm('Confirm delete?');">Delete</a>
						<?php endif; ?>
				  </div>
					<?php endif; ?>
					
				</td>
				<td> 
					<input type="submit" value="submit" class="btn btn-small btn-success">
				</td>
			</tr>

			<?php require "hasOne.php"; ?>

			<?php require "children.php"; ?>
			
			</form>
	</table>
</div>