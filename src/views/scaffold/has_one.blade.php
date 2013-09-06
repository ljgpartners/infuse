<?php use Bpez\Infuse\Util; ?>

@if (isset($header['edit']) && $header['hasOneAssociation'] != false)
<tr>

	<?php
		$hasOneAssociation = $header['hasOneAssociation'];
		$model = key($hasOneAssociation);
		$hasOneAssociation = current($hasOneAssociation);
		$childTitle = $hasOneAssociation[0];
		$childColumns = $hasOneAssociation[1];
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
			<th>{{$column}}</th>
			@endforeach
			<th>
				@if ($entries->hasOne(ucfirst($model))->count() == 0)
					<a href="{{Util::getPath()."/".$model}}?action=c&pid={{$entries->id}}&parent={{Util::classToString($entries)}}&oneToOne=1">Create one</a>
				@endif
			</th>
		</tr>
				
		@foreach ($entries->hasOne(ucfirst($model))->get() as $key => $child)
		<tr>
			@foreach ($childColumns as $column)
			<td>{{$child->{$column} }}</td>
			@endforeach
			<td>
				<a href="{{Util::getPath()."/".$model}}?action=s&id={{$child->id}}&pid={{$entries->id}}&parent={{Util::classToString($entries)}}">show</a>
				<a href="{{Util::getPath()."/".$model}}?action=e&id={{$child->id}}&pid={{$entries->id}}&parent={{Util::classToString($entries)}}">edit</a>
				<a href="{{Util::getPath()."/".$model}}?action=d&id={{$child->id}}&pid={{$entries->id}}>&parent={{Util::classToString($entries)}}" onclick="return confirm('Confirm delete?');">delete</a>
			</td>
		</tr>
		@endforeach


	</table>



</tr>
@endif