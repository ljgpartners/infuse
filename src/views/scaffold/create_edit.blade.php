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
	
	<?php $errors = Util::flashArray("errors"); ?>
	<?php $fileErrors = Util::flashArray("file_errors"); ?>

	{{Util::fuseAlerts(Util::flash())}}

	<table class="table table-striped table-bordered">
			<form method="post" action="?{{(Util::get("stack"))? "stack=".Util::get("stack") : ""}}" enctype="multipart/form-data">

				
			@if (Util::get("id") && Util::get("action") != "cd")
				<input type="hidden" name="action" value="u">
				<input type="hidden" name="id" value="{{Util::get("id")}}">
			@else
				<input type="hidden" name="action" value="cu">
			@endif

			@if (Util::get("stack"))
				<input type="hidden" name="{{Util::foreignKeyString(Util::stackParentName())}}" value="{{Util::stackParentId()}}">
				@if (Util::get("oneToOne"))
					<input type="hidden" name="oneToOne" value="{{Util::stackParentName()}}">
				@endif
			@endif



			<!-- Iterate through all columns -->

			@foreach ($columns as $column)

			<!-- Hide certain fields -->
			@if ($column['field'] != "created_at" && $column['field'] != "updated_at" && !Util::isForeignKey($column['field']) && Util::checkInfuseLoginFields($infuseLogin, $column) )

			<tr> 
				@if (array_key_exists($column['field'], $header['columnNames']))
					<th>{{$header['columnNames']["{$column['field']}"]}}</th>
				@else
					<th>{{Util::cleanName($column['field'])}}</th>
				@endif

				<td> 

				@if ($column['field'] == Util::getForeignKeyString($entries))
				
				@elseif (array_key_exists("ckeditor", $column))
					<textarea class="ckeditor" name="{{$column['field']}}">{{$entries->{$column['field']} }}</textarea>

				@elseif (array_key_exists("select", $column))

					<select name="{{$column['field']}}">
						@if (array_key_exists("select_blank", $column))
							<option value=""></option>
						@endif
						@foreach ($column['select'] as $value)
								<?php $columnName = end($value); ?>
								@if ($entries->{$column['field']} == $value["id"])
									<option value="{{$value["id"]}}" selected="selected">{{$columnName}}</option>
								@else
									<option value="{{$value["id"]}}">{{$columnName}}</option>
								@endif
						@endforeach
					</select>

				@elseif (array_key_exists("multi_select", $column))

					<?php
						$dataMultiSelect = array();
						foreach ($column['multi_select'] as $value):
								$columnName = end($value);
								array_push($dataMultiSelect, array("id" => $value["id"], "value" => $columnName));
						endforeach;
					?>
					
					<div class="multiSelect"  data-name="{{$column['field']}}" data-data='{{json_encode($dataMultiSelect)}}' data-value="{{$entries->{$column['field']} }}"></div>
					<input class="multiSelect{{$column['field']}}" name="{{$column['field']}}" type="hidden" value='{{$entries->{$column['field']} }}'>


				@elseif (array_key_exists("upload", $column))

					<input type="file" name="{{$column['field']}}" class="{{(($column['upload']['imageCrop'])? "livePreviewCrop": "" )}}" id="upload{{$column['field']}}" >
					
					@if (property_exists($entries, $column['field']) && $entries->{$column['field']} != "")
						</br>
						<button type="button" class="btn btn-mini btn-link" data-toggle="modal" data-target="#{{"Modal".$column['field'].$entries->id}}">
							View current
						</button>

						<div id="{{"Modal".$column['field'].$entries->id}}" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						  <div class="modal-header">
						    <h3 id="myModalLabel">{{$entries->{$column['field']} }}</h3>
						  </div>
						  <div class="modal-body">
						    <img src="{{$entries->url($column['field'])}}">
						  </div>
						</div>
					@endif

					@foreach ($column['upload']['validations'] as $val)
						@if ($fileErrors && array_key_exists($column['field'], $fileErrors) && $val[1] == $fileErrors["{$column['field']}"])
							<?php unset($fileErrors["{$column['field']}"]); ?>
							</br><span class="label label-important">{{$val[1]}}</span>
						@else
						</br><span class="label label-info">{{$val[1]}}</span>
						@endif
					@endforeach

					@if ($column['upload']['imageCrop'])
					<div class="imagePreviewCrop">
						<!-- image preview area-->
						<img id="upload{{$column['field']}}Preview" class="imgAreaSelect" data-id="upload{{$column['field']}}" style="display:none;"/>
					</div>

					<!-- hidden inputs -->
					<input type="hidden" id="upload{{$column['field']}}x" name="upload{{$column['field']}}x" />
					<input type="hidden" id="upload{{$column['field']}}y" name="upload{{$column['field']}}y" />
					<input type="hidden" id="upload{{$column['field']}}w" name="upload{{$column['field']}}w" />
					<input type="hidden" id="upload{{$column['field']}}h" name="upload{{$column['field']}}h" />

					<input type="hidden" id="upload{{$column['field']}}nw" name="upload{{$column['field']}}nw" />
					<input type="hidden" id="upload{{$column['field']}}nh" name="upload{{$column['field']}}nh" />

					@endif

				@else

					<?php switch ($column['type']):
							case 'varchar': ?>
							<input type="text" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}">
					<?php 	break;
							case 'text': ?>
							<textarea name="{{$column['field']}}">{{$entries->{$column['field']} }}</textarea>
					<?php 	break;
							case 'datetime':
							case 'timestamp': ?>
							<input type="text" class="selectedDateTime" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}">
					<?php 	break; 
							case 'date': ?>
							<input type="text" class="selectedDate" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}">
					<?php 	break;
							case 'int': ?>
							<input type="text" name="{{$column['field']}}" pattern="\d+" value="{{$entries->{$column['field']} }}"/>
					<?php 	break;
							case 'tinyint': ?> 
							<select name="{{$column['field']}}">
								<option value="0" {{($entries->{$column['field']} == 0)? 'selected="selected"' : ""}} >No</option>
								<option value="1" {{($entries->{$column['field']} == 1)? 'selected="selected"' : ""}} >Yes</option>
							</select>
					<?php break;
							default: ?>
								<input type="text" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}">
					<?php		
						endswitch;
					?>

				@endif

				
				@if (array_key_exists("description", $column))
					</br><span class="label label-info">{{$column['description']}}</span>
				@endif
				
				@if ($errors && $errors->has("{$column['field']}"))
						@foreach ($errors->get("{$column['field']}") as $err)
							</br><span class="label label-important">{{$err}}</span>
						@endforeach
				@endif



				@if ($fileErrors && array_key_exists($column['field'], $fileErrors) && count($fileErrors) > 0)
					@foreach ($fileErrors as $err)
							</br><span class="label label-important">{{$err}}</span>
					@endforeach
				@endif




				</td>
			</tr>
			@endif
			@endforeach

			<tr>
				<td>
					@if (Util::get("stack"))
					<div class="btn-group">
				    <a class="btn btn-small" href="{{Util::childBackLink()}}">Back</a>
				  	@if (isset($header['edit']))
				  	<a class="btn btn-small" href="{{Util::childActionLink(Util::get("stack"), 's', $entries->id)}}">Show</a>
					  	@if(!$header['onlyOne'])
							<a class="btn btn-small" href="{{Util::childActionLink(Util::get("stack"), 'd', $entries->id)}}" onclick="return confirm('Confirm delete?');">Delete</a>
							@endif
						@endif
				  </div>
					@else
					<div class="btn-group">
				    <a class="btn btn-small" href="?action=l">List</a>
				  	@if (isset($header['edit']))
				  	<a class="btn btn-small" href="?action=s&id={{$entries->id}}">Show</a>
					  	@if(!$header['onlyOne'])
							<a class="btn btn-small" href="?action=d&id={{$entries->id}}" onclick="return confirm('Confirm delete?');">Delete</a>
							@endif
						@endif
				  </div>
					@endif
					
				</td>
				<td> 
					<input type="submit" value="submit" class="btn submitButton">
				</td>
			</tr>

			@include('infuse::scaffold._has_one')

			@include('infuse::scaffold._children')

			@include('infuse::scaffold._many_to_many')
			
			</form>
	</table>
</div>