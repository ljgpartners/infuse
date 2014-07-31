

@if (isset($header['edit']) && count($header['associations']) > 0)
<tr>

	@foreach ($header['associations'] as $association)
	<?php
			$model = $association[0];
			$childTitle = $association[1];
			$childColumns = $association[2];
			$childOrderColumn = (isset($association[3]) && is_array($association[3]) && isset($association[3]['order_column']) )? $association[3]['order_column'] : false;
			$childOrderDirection = (isset($association[3]) && is_array($association[3]) && isset($association[3]['order_direction']) )? $association[3]['order_direction'] : false;
			$importCSV = (isset($association[3]) && is_array($association[3]) && isset($association[3]['import_csv']) )? $association[3]['import_csv'] : false;
			$header['deleteAction'] = (isset($association[3]) && is_array($association[3]) && isset($association[3]['delete_action']) )? $association[3]['delete_action'] : true;

			$importCSVFunction = (isset($association[3]) && is_array($association[3]) && isset($association[3]['import_csv_function']) )? $association[3]['import_csv_function'] : false;
			$importCSVFunctionUrl = (isset($association[3]) && is_array($association[3]) && isset($association[3]['import_csv_function_url']) )? $association[3]['import_csv_function_url'] : false; 
			$importCSVFunctionText = (isset($association[3]) && is_array($association[3]) && isset($association[3]['import_csv_function_text']) )? $association[3]['import_csv_function_text'] : false;

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
				<a href="{{Util::childActionLink($model, 'c')}}">Create</a>
			</th>
		</tr> 
		
		<?php 
			if ($childOrderColumn && $childOrderDirection ) {
				$hasManyObject = $entries->hasMany(Util::under2camel(ucfirst($model)))->orderBy($db::raw("{$childOrderColumn} = 0"), "asc")->orderBy($childOrderColumn, $childOrderDirection)->get();
			} else {
				$hasManyObject = $entries->hasMany(Util::under2camel(ucfirst($model)))->get();
			}
		?>
		
		
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

		@if ($importCSV)  
		<tr>
			<th colspan="{{$numColumns}}">
				<form method="POST"  enctype="multipart/form-data">
					Import CSV  (Excel Template: <a target="_BLANK" href="{{$importCSV}}">{{Util::camel2under($model)}}</a>)
					<pre> <input type="file" name="csv_file">  </pre>
					<input type="hidden" name="action" value="icsv">
					<input type="hidden" name="child" value="{{$model}}">
					<input type="hidden" name="back" value="{{$_SERVER['REQUEST_URI']}}">
					<input type="hidden" name="parent_id" value="{{$entries->id}}"> 
					<input type="submit" value="import" class="btn submitButton">
				</form>
			</th>
		</tr>
		@endif

		@if ($importCSVFunction)  
		<tr>
			<th colspan="{{$numColumns}}">
				<form method="POST"  enctype="multipart/form-data">
					{{(($importCSVFunctionText)? $importCSVFunctionText : "Import CSV  (Excel Template: " )}}
					@if ($importCSVFunctionUrl)
						<a target="_BLANK" href="{{$importCSVFunctionUrl}}">example template</a>)
					@endif
					<pre> <input type="file" name="csv_file">  </pre>
					<input type="hidden" name="action" value="icsvc">
					<input type="hidden" name="function" value="{{$importCSVFunction}}">
					<input type="hidden" name="back" value="{{$_SERVER['REQUEST_URI']}}">
					<input type="hidden" name="parent_id" value="{{$entries->id}}"> 
					<input type="submit" value="import" class="btn submitButton">
				</form>
			</th>
		</tr>
		@endif


		


	</table>
	@endforeach


</tr>
@endif