@if (isset($header['edit']) && $header['hasOneAssociation'] != false)
<tr>
	@foreach ($header['hasOneAssociation'] as $association)
	<?php
		$association = $association;
		$model = $association[0];
		$childTitle = $association[1];
		$childColumns = $association[2];
		$header['deleteAction'] = (isset($association[3]) && is_array($association[3]) && isset($association[3]['delete_action']) )? $association[3]['delete_action'] : true;
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

			<th>
				@if ($entries->hasOne(ucfirst($model))->count() == 0)
					<a href="{{Util::childActionLink($model, 'c')}}">Create</a>
				@endif
			</th>
		</tr>
				
		@foreach ($entries->hasOne(ucfirst($model))->get() as $key => $child)
		<tr>

			@foreach ($childColumns as $column)
				@if (is_array($column)) 
					<td>
					@foreach (current($column) as $value)
							@if ($child->{key($column)} == $value["id"])
								{{end($value)}}
							@endif
					@endforeach
					</td>
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
						<td>{{$child->{$column} }}</td>
					@endif
					
				@endif
			@endforeach

			<td>
				<a href="{{Util::childActionLink($model, 's', $child->id)}}">show</a>
				<a href="{{Util::childActionLink($model, 'e', $child->id)}}">edit</a>
				@if ($header['deleteAction'])
					<a href="{{Util::childActionLink($model, 'd', $child->id)}}" onclick="return confirm('Confirm delete?');">delete</a>
				@endif
			</td>
		</tr>
		@endforeach


	</table>


	@endforeach
</tr>
@endif