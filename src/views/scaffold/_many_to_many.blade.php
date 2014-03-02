<?php
use \DB;

$entry = $data['enrties'];
$columns = $data['columns'];
$header  = $data['header'];
$infuseLogin = $data['infuseLogin']; 

?>

@if (isset($header['edit']) && count($header['manyToManyAssociations']) > 0)
<tr>
<?php

$model = (array_key_exists('actualModel', $header))? get_class($header['actualModel']) : get_class($entry);

?>

	@foreach ($header['manyToManyAssociations'] as $association)
	<?php
		$firstModel = $association[0];
		$secondModel = $association[2];
		$manyToManyTable = $association[4];

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
	?>
	<table class="table table-striped table-bordered manyToManyAssociations">
		<tr>
			<td>
				<h4>{{Util::cleanName(Util::camel2under($belongsToModel))}}</h4>  
			</td>
		</tr>

		<tr>
			<td>
				<?php
					$belongsToModelInstance = Util::stringToCLass($belongsToModel);
					$ids = DB::table($manyToManyTable)->where($secondForeignId, "=", $entry->id)->lists($firstForeignId);
					$allPossible = DB::table($belongsToModelInstance->getTable())->select('id', $columnName)->orderBy($columnName, 'asc')->get();
				?>

				@foreach ($allPossible as $a)
						@if ($belongsToModel != "InfuseUser" && $a->id != 1)
						<label class="checkbox inline">
						  <input type="checkbox" {{((in_array($a->id, $ids))? "checked='checked'" : "" )}} name="{{$manyToManyTable}}[]" value="{{$a->id}}"> {{$a->{$columnName} }}
						</label>
						@endif
				@endforeach
			</td>
		</tr>

	</table>
	@endforeach

</tr>
@endif