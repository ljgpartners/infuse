@foreach ($entries as $entry) 

<tr class="addedRow">
	@foreach ($columns as $column)
	<td>
		@if ($columns[0] == $column)
			<span class="iconImportAccordion icon-chevron-right"></span>
		@endif
		{{$entry->{$column} }}
	</td>
	@endforeach
	
	@if ($updatedAt)
		@if ($updatedAt <= $entry->updated_at)
		<td class="alert-info">
			<div class="alert-info importedUpdatedFlag">Newly Updated  </div>
		</td>
		@else
		<td></td>
		@endif

	@else
	<td class="alert-info">
		<div class="alert-info importedUpdatedFlag">Newly Updated  </div>
	</td>
	@endif
	
	
</tr>


<tr class="importModalAccordion">
	<td colspan="3">
		<table class="table table-bordered table-striped">
			@foreach ($map as $key => $value)
			<tr>
				<td>
					@if (Util::splitReturnFirst($key, "@"))

						@if (Util::splitReturnSecond($key, "@") == "image")

							@if (!empty($entry->{Util::splitReturnFirst($key, "@")})) 
							<div class="previewImage">
								<input type="checkbox" checked="checked" class="checkAll" data-value="{{$entry->url(Util::splitReturnFirst($key, "@")) }}" data-overite-column="{{$value}}" data-attachment="image"> 
							</div>
							@endif

						@else
							<input type="checkbox" checked="checked" class="checkAll" data-value="{{$entry->{Util::splitReturnFirst($key, "@")} }}" data-overite-column="{{$value}}" data-attachment="{{Util::splitReturnSecond($key, "@")}}">
						@endif

					@else 
						<input type="checkbox" checked="checked" class="checkAll" data-value="{{$entry->{$key} }}" data-overite-column="{{$value}}" data-attachment="0">
					@endif
					
				</td>
				<th>
					@if (Util::splitReturnFirst($key, "@"))
						<span>{{Util::cleanName(Util::splitReturnFirst($key, "@"))}}</span>
					@else 
						<span>{{Util::cleanName($key)}}</span>
					@endif
				</th>
				<td>
					@if (Util::splitReturnFirst($key, "@"))
						@if (Util::splitReturnSecond($key, "@") == "image")
							@if (!empty($entry->{Util::splitReturnFirst($key, "@")})) 
							<div class="previewImage">
								<img src="{{$entry->url(Util::splitReturnFirst($key, "@"))}}" alt="">  
							</div>
							@endif
						@else
							{{$entry->{Util::splitReturnFirst($key, "@")} }}
						@endif

					@else 
						{{$entry->{$key} }}
					@endif

				</td>
			</tr>
			@endforeach

			<tr>
				<td colspan="3">
					<a class="importEntry btn submitButton" data-modal-id="{{$id}}">IMPORT</a>
					<a class="importCheckAll btn submitButton" data-all-on="1">Uncheck All</a>
				</td>
			</tr>
		</table>
	</td>
</tr>

@endforeach