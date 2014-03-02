<?php 
use \DB;

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
			$childOrderColumn = (isset($association[3]) && is_array($association[3]) && isset($association[3]['order_column']) )? $association[3]['order_column'] : false;
			$childOrderDirection = (isset($association[3]) && is_array($association[3]) && isset($association[3]['order_direction']) )? $association[3]['order_direction'] : false;
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
				@elseif (Util::splitReturnFirst(Util::cleanName($column), "@"))
					<th>{{Util::splitReturnFirst(Util::cleanName($column), "@")}}</th>
				@else
					<th>{{Util::cleanName($column)}}</th>
				@endif
			@endforeach 
			<th><a href="{{Util::childActionLink($model, 'c')}}">Create</a></th>
		</tr> 
		
		<?php 
			if (array_key_exists('actualModel', $header)):
				if ($childOrderColumn && $childOrderDirection ) {
					$hasManyObject = $header['actualModel']->hasMany(Util::under2camel(ucfirst($model)))->orderBy(DB::raw("{$childOrderColumn} = 0"), "asc")->orderBy($childOrderColumn, $childOrderDirection)->get();
				} else {
					$hasManyObject = $header['actualModel']->hasMany(Util::under2camel(ucfirst($model)))->get();
				}
			else:
				if ($childOrderColumn && $childOrderDirection ) {
					$hasManyObject = $entries->hasMany(Util::under2camel(ucfirst($model)))->orderBy(DB::raw("{$childOrderColumn} = 0"), "asc")->orderBy($childOrderColumn, $childOrderDirection)->get();
				} else {
					$hasManyObject = $entries->hasMany(Util::under2camel(ucfirst($model)))->get();
				}
			endif;
		?>
		
		<?php $previoustChildId = 0; ?>
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
					@if (Util::splitReturnFirst($column, "@"))
						<td>
							@if ($child->{Util::splitReturnFirst($column, "@")} != "")
							<div class="previewImage">
								<img src="{{$child->url(Util::splitReturnFirst($column, "@"))}}" alt="">  
							</div>
							@endif
						</td>
					@else
						@if ($childOrderColumn && $childOrderDirection && ($childOrderColumn == $column))
							<td class="childOrderColumn">
								<a class="childUpOrder" data-previous-id="{{$previoustChildId}}" data-id="{{$child->id}}" data-url="{{$_SERVER['REQUEST_URI']}}" data-column="{{$column}}" data-model="{{get_class($child)}}" href="">[up]</a> 
								<a class="childDownOrder" href="">[down]</a>
								<span>{{$child->{$column} }}</span>
							</td>
						@else
							<td>{{$child->{$column} }}</td>
						@endif
						
					@endif
					
				@endif
			
			@endforeach 
			<td>
				<a href="{{Util::childActionLink($model, 's', $child->id)}}">show</a>
				<a href="{{Util::childActionLink($model, 'e', $child->id)}}">edit</a>
				<a href="{{Util::childActionLink($model, 'd', $child->id)}}" onclick="return confirm('Confirm delete?');">delete</a>
			</td>
		</tr>
		<?php $previoustChildId = $child->id; ?>	
		@endforeach


	</table>
	@endforeach


</tr>
@endif