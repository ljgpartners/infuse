<div class="sectionNavigation">
	<div class="sectionInfo">
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-6">
					<h1>{{$header['name']}}</h1>
					<p>{{$header['description']}}</p>
				</div>
				<div class="col-sm-6">
					@include('infuse::scaffold._breadcrumbs')
				</div>
		  </div>
		</div>
	</div>
</div> <!-- end of .sectionNavigation -->

<div class="container-fluid">

	<div class="row">
  	<div class="col-sm-12 col-xs-12">
		{!!Util::fuseAlerts(Util::flash())!!} 
		</div>
	</div>

	<table class="table table-striped table-bordered">
			@foreach ($columns as $column)

			<?php $columnValue = Util::getColumnValue($entries, $column); ?>

			<tr>

				@if (array_key_exists("select", $column))
					<?php $selectArray = (array_key_exists("nested", $column) && isset($column['nested_last_array']))? $column['nested_last_array'] : $column['select'] ; ?>
					@foreach ($selectArray as $value)
							@if ($columnValue == $value["id"])
								<?php $columnName = end($value); ?>
								<th>{{Util::cleanName($column['field'])}}</th>
								<td>{{$columnName}}</td>
							@endif
					@endforeach
					
				@elseif (array_key_exists("upload", $column))
					<th>{{Util::cleanName($column['field'])}}</th>
					<td>
						@if ($columnValue != "" && preg_match('/(\.jpg|\.png|\.gif|\.JPG|\.PNG|\.GIF)$/', $columnValue ))
							<img class="" src="{{$entries->uploadPath($column['field']).$columnValue}}">
						@elseif ($columnValue != "")
							<a href="{{$entries->uploadPath($column['field']).$columnValue}}">{{$columnValue }}</a>
						@endif
					</td>

				@else

				@if (array_key_exists($column['field'], $header['columnNames']))
					<th>{{$header['columnNames']["{$column['field']}"]}}</th>
				@elseif ($column['field'] == "infuse_user_id") 
					<th>User</th>
				@else
					<th>{{Util::cleanName($column['field'])}}</th>
				@endif

				@if ($column['field'] == "created_at" || $column['field'] == "updated_at")
					<td>{{$columnValue->tz(\Config::get('app.timezone'))->format($header['formatLaravelTimestamp']) }}</td>
				@elseif ($column['field'] == "infuse_user_id")
					<?php 
						try {
							$name = InfuseUser::findOrFail($columnValue)->full_name;
						} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
							$name = "User not found";
						}
					?>
					<td>{{$name}}</td>
				@else
					<td>{!! $columnValue !!}</td>
				@endif

				

				@endif
			</tr>
			@endforeach

			

	</table>

	<nav class="bottom">
			@if (Util::get("stack"))
		    <a class="" href="{{Util::childBackLink()}}">Back</a>
	  		<a class="" href="{{Util::childActionLink(Util::get("stack"), 'e', $entries->id)}}">Edit</a>
		  	@if(!$header['onlyOne'] && $header['deleteAction'])
				<a class="" href="{{Util::childActionLink(Util::get("stack"), 'd', $entries->id)}}" onclick="return confirm('Confirm delete?');">Delete</a>
				@endif

			@else
		    <a class="" href="?action=l">List</a>
		  	<a class="" href="?action=e&id={{$entries->id}}">Edit</a>
		  	@if(!$header['onlyOne'] && $header['deleteAction'])
				<a class="" href="?action=d&id={{$entries->id}}" onclick="return confirm('Confirm delete?');">Delete</a>
				@endif
			@endif
	</nav>

</div>
