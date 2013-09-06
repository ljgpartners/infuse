<?php 
use Bpez\Infuse\Util; 

$entries = $data['enrties'];
$columns = $data['columns'];
$header  = $data['header'];
$infuseLogin = $data['infuseLogin'];
?>

@if (isset($header['edit']) && count($header['associations']) > 0)
<tr>

	@foreach ($header['associations'] as $association)
	<?php
			$model = $association[0];
			$childTitle = $association[1];
			$childColumns = $association[2];
			$numColumns = count($childColumns)+1;
	?>
	<table class="table table-striped table-bordered">
		<tr>
			<td colspan="{{$numColumns}}">
				<h4>{{$childTitle}}</h4>
			</td>
		</tr>
		<tr>
			@foreach ($childColumns as $column)
				@if (is_array($column))
					<th>{{Util::cleanName(key($column))}}</th>
				@else
					<th>{{Util::cleanName($column)}}</th>
				@endif
			@endforeach
			<th><a href="{{Util::getPath()."/".Util::camel2under($model)}}?action=c&pid={{$entries->id}}&parent={{Util::classToString($entries)}}">Create </a></th>
		</tr>
		
		<?php 
			if (array_key_exists('actualModel', $header)):
				$hasManyObject = $header['actualModel']->hasMany(Util::under2camel(ucfirst($model)))->get();
			else:
				$hasManyObject = $entries->hasMany(Util::under2camel(ucfirst($model)))->get();
			endif;
		?>
		
		@foreach ($hasManyObject as $key => $child)
		<tr>
			@foreach ($childColumns as $column)
				@if (is_array($column))
					@foreach (current($column) as $value)
							@if ($child->{key($column)} == $value["id"])
								<td>{{end($value)}}</td>
							@endif
					@endforeach
				@else
					<td>{{$child->{$column} }}</td>
				@endif

			@endforeach
			<td>
				<a href="{{Util::getPath()."/".Util::camel2under($model)}}?action=s&id={{$child->id}}&pid={{$entries->id}}&parent={{Util::classToString($entries)}}">show</a>
				<a href="{{Util::getPath()."/".Util::camel2under($model)}}?action=e&id={{$child->id}}&pid={{$entries->id}}&parent={{Util::classToString($entries)}}">edit</a>
				<a href="{{Util::getPath()."/".Util::camel2under($model)}}?action=d&id={{$child->id}}&pid={{$entries->id}}&parent={{Util::classToString($entries)}}" onclick="return confirm('Confirm delete?');">delete</a>
			</td>
		</tr>
		@endforeach


	</table>
	@endforeach


</tr>
@endif