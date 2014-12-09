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
		<a href="{{Util::childActionLink($model, 'c')}}">Create</a>
	</th>
</tr>


@foreach ($hasManyObject as $key => $child)
<tr data-class="{{$model}}" class="{{$model}}"> 
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
				@if ($childOrderColumn && $childOrderDirection && ($childOrderColumn == $column))
					<td class="childOrderColumn">
						<a class="childUpOrder" data-id="{{$child->id}}" data-url="{{$_SERVER['REQUEST_URI']}}" data-column="{{$column}}" data-model="{{get_class($child)}}" href="">[up]</a> 
						<a class="childDownOrder" data-id="{{$child->id}}" data-url="{{$_SERVER['REQUEST_URI']}}" data-column="{{$column}}" data-model="{{get_class($child)}}" href="">[down]</a>
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
		@if ($header['deleteAction'])
			<a href="{{Util::childActionLink($model, 'd', $child->id)}}" onclick="return confirm('Confirm delete?');">delete</a>
		@endif
	</td>
</tr>
@endforeach