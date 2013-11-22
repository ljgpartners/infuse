<?php 
use Bpez\Infuse\Util; 

$entries = $data['enrties'];
$columns = $data['columns'];
$header  = $data['header'];
$infuseLogin = $data['infuseLogin'];

?>
<div class="infuseScaffold">

	<div class="page-header">
	  <h1>{{$header['name']}} <small> {{$header['description']}}</small></h1>
	</div>

	{{Util::fuseAlerts(Util::flash())}}

	<table class="table table-striped table-bordered">
			@foreach ($columns as $column)
			<tr>

				@if (array_key_exists("select", $column))
						@foreach ($column['select'] as $value)
								@if ($entries->{$column['field']} == $value["id"])
									<?php $columnName = end($value); ?>
									<th>{{Util::cleanName($column['field'])}}</th>
									<td>{{$columnName}}</td>
								@endif
						@endforeach
				@elseif (array_key_exists("upload", $column))
					<th>{{Util::cleanName($column['field'])}}</th>
					<td>
						@if ($entries->{$column['field']} != "" && preg_match('/(\.jpg|\.png|\.gif|\.JPG|\.PNG|\.GIF)$/', $entries->{$column['field']} ))
							<img class="" src="{{$entries->url($column['field'])}}">
						@elseif ($entries->{$column['field']} != "")
							<a href="<?php echo $entries->url($column['field']); ?>">{{$entries->{$column['field']} }}</a>
						@endif
					</td>

				@else

				@if (array_key_exists($column['field'], $header['columnNames']))
					<th>{{$header['columnNames']["{$column['field']}"]}}</th>
				@else
					<th>{{Util::cleanName($column['field'])}}</th>
				@endif
				<td>{{$entries->{$column['field']} }}</td>

				@endif
			</tr>
			@endforeach

			<tr>
				<td colspan="2"> 
					@if (Util::get("parent") && Util::get("pid"))
					<div class="btn-group">
					    <a class="btn btn-small" href="{{Util::redirectBackToParentUrl(Util::classToString($entries), Util::get("pid"))}}">Back</a>
					    <a class="btn btn-small" href="?action=e&id={{$entries->id}}&pid={{Util::get("pid")}}&parent={{Util::get("parent")}}">Edit</a>
							<a class="btn btn-small" href="?action=d&id={{$entries->id}}&pid={{Util::get("pid")}}&parent={{Util::get("parent")}}" onclick="return confirm('Confirm delete?');">Delete</a>
					</div>
					@else
					<div class="btn-group">
					    <a class="btn btn-small" href="?action=l">List</a>
					    <a class="btn btn-small" href="?action=e&id={{$entries->id}}">Edit</a>
							<a class="btn btn-small" href="?action=d&id={{$entries->id}}" onclick="return confirm('Confirm delete?');">Delete</a>
					</div>
					@endif
				</td>
			</tr>

	</table>
</div>