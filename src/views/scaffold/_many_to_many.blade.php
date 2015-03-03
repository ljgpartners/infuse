<?php 
function convert($size)
		{
		    $unit=array('b','kb','mb','gb','tb','pb');
		    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		}

?>
@if (isset($header['edit']) && count($header['manyToManyAssociations']) > 0)
<div class="row">
	<div class="col-sm-12 col-xs-12">
	<div class="table-responsive">

<?php Debugbar::addMessage(convert(memory_get_usage(true)), 'mem start');  ?>

<?php
$entry = $entries;
//$model = (array_key_exists('actualModel', $header))? get_class($header['actualModel']) : get_class($entry);
$model = get_class($entry);
?>

	@foreach ($header['manyToManyAssociations'] as $association)
	<?php
		$firstModel = $association[0];
		$secondModel = $association[2];
		$manyToManyTable = $association[4];

		if (isset($association[7]) && is_array($association[7])){
			$extraConfig = $association[7];
			if (isset($extraConfig['callBackDisplayCheck'])) {
				$callBackDisplayCheck = $extraConfig['callBackDisplayCheck'];
			} else {
				$callBackDisplayCheck = function() { return true; };
			}
			if (isset($extraConfig['addColumnToSelect'])) {
				$addColumnToSelect = $extraConfig['addColumnToSelect'];
			}

			if (isset($extraConfig['sectionName'])) {
				$sectionName = $extraConfig['sectionName'];
			}
			
		} else {
			$callBackDisplayCheck = function() { return true; };
		}

		
		 

		if ($model == $firstModel) {
			$belongsToModel = $secondModel;
			$firstForeignId =	$association[3];
			$secondForeignId = $association[1];
			$columnName = $association[6];
		} else if ($model == $secondModel) {
			$belongsToModel = $firstModel;
			$firstForeignId =	$association[1];
			$secondForeignId = $association[3];
			$columnName = $association[5];
		}

		Debugbar::addMessage(convert(memory_get_usage(true)), 'mem setup');
	?>
	<table class="table table-striped table-bordered manyToManyAssociations">
		<tr>
			<td>
				<h4>{{(isset($sectionName))? $sectionName  : Util::cleanName(Util::camel2under($belongsToModel)) }}</h4>  
			</td>
		</tr>

		<tr>
			<td>
				<?php
					$belongsToModelInstance = Util::stringToCLass($belongsToModel);
					$ids = $db::table($manyToManyTable)->where($secondForeignId, "=", $entry->id)->lists($firstForeignId);
					Debugbar::addMessage(convert(memory_get_usage(true)), 'mem ids');
					if ($belongsToModel == "InfuseRole") {

						if (isset($addColumnToSelect)) {
							$allPossible = $db::table($belongsToModelInstance->getTable())->select('id', $columnName, "level", $addColumnToSelect);
						} else {
							$allPossible = $db::table($belongsToModelInstance->getTable())->select('id', $columnName, "level");
						}
						$allPossible = $allPossible->orderBy('level', 'asc')->get();

					} else if ($belongsToModel == "InfuseUser") {

						if (isset($addColumnToSelect)) {
							$allPossible = $belongsToModelInstance::select('id', $columnName, $addColumnToSelect);
						} else {
							$allPossible = $belongsToModelInstance::select('id', $columnName);
						}
						$allPossible =	$allPossible->orderBy($columnName, 'asc')->get();

					} else {
						$allPossible = $db::table($belongsToModelInstance->getTable())->select('id', $columnName)->orderBy($columnName, 'asc')->get();
					}
					Debugbar::addMessage(convert(memory_get_usage(true)), 'mem end ids');
				?>

				
			    

			  

			  <?php 
			  $count = count($allPossible);
			  $perColumn = ceil($count/4);
			  $count = 1;
			  $oneRolePerUser = (\Config::get("infuse::config.one_role_per_user"))? "oneRolePerUser" : "";
			  Debugbar::addMessage(convert(memory_get_usage(true)), 'mem after oneRolePerUser');


			  $role = $user->roles()->orderBy("level", "asc")->limit(1)->first(); 
				$level = (count($role) == 1)? (int)$role->level : 0;
				Debugbar::addMessage(convert(memory_get_usage(true)), 'mem after role level');
			  ?>
			  <div class="form-group"> 
				@foreach ($allPossible as $a)
					@if (!($belongsToModel == "InfuseUser" && $a->id == 1) && !($belongsToModel == "InfuseRole" && $a->id == 1)  && !($belongsToModel == "InfusePermission" && $a->id == 1) )
					<?php Debugbar::addMessage(convert(memory_get_usage(true)), 'mem foreach 1'); ?>
						<?php $count = ($count > $perColumn)? 1 : $count; ?>

						@if ($count == 1)
						 <div class="controls span2">
						@endif

						@if ($belongsToModel == "InfuseRole") 

							@if ($user->level($a->level, '<') || $user->level(1, '=') && $callBackDisplayCheck($a, $user))

							<label class="checkbox">
							  <input class="{{$oneRolePerUser}}" type="checkbox" {{((in_array($a->id, $ids))? "checked='checked'" : "" )}} name="{{$manyToManyTable}}[]" value="{{$a->id}}"> {{$a->{$columnName} }}
							</label>
							@elseif (in_array($a->id, $ids)) 
								<input type="hidden" name="{{$manyToManyTable}}[]"  value="{{$a->id}}">
							@endif

						@elseif ($belongsToModel == "InfuseUser") 

							@if (($a->level($level, ">") || $user->level(1, '='))  && $callBackDisplayCheck($a, $user))
							<?php Debugbar::addMessage(convert(memory_get_usage(true)), 'mem foreach after level check'); ?>
							<label class="checkbox">
							  <input type="checkbox" {{((in_array($a->id, $ids))? "checked='checked'" : "" )}} name="{{$manyToManyTable}}[]" value="{{$a->id}}"> {{$a->{$columnName} }}
							</label>
							@elseif (in_array($a->id, $ids)) 
								<input type="hidden" name="{{$manyToManyTable}}[]"  value="{{$a->id}}">
							@endif

						@else
							<label class="checkbox">
							  <input type="checkbox" {{((in_array($a->id, $ids))? "checked='checked'" : "" )}} name="{{$manyToManyTable}}[]" value="{{$a->id}}"> {{$a->{$columnName} }}
							</label>
						@endif

						@if ($count == $perColumn)
						 </div>
						@endif

						<?php $count++; ?>
					@endif
				@endforeach

				@if (isset($hidden))
					@foreach ($hidden as $u)
						@if (in_array($u->id, $ids))
							<input type="hidden" name="{{$manyToManyTable}}[]"  value="{{$u->id}}">
						@endif
					@endforeach
				@endif

				</div>
			</td>
		</tr>

	</table>
	@endforeach

	</div>
	</div>
</div>
<?php Debugbar::addMessage(convert(memory_get_usage(true)), 'mem end');  ?>
@endif


