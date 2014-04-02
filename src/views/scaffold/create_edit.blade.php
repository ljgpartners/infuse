<div class="infuseInner">

<div class="infuseScaffold">
	
	<div class="page-header">
	  <h1>{{$header['name']}} <small> {{$header['description']}}</small></h1>
	</div>
	
	<?php $errors = Util::flashArray("errors"); ?>
	<?php $fileErrors = Util::flashArray("file_errors"); ?>

	{{Util::fuseAlerts(Util::flash())}} 

	@include('infuse::scaffold._import_from_models')


	<table class="table table-striped table-bordered editCreateForm"> 
			<form method="post" action="?" enctype="multipart/form-data">

			{{-- Added infuse action and id to the form --}}

			@if (Util::get("id") && Util::get("action") != "cd")
				<input type="hidden" name="action" value="u">
				<input type="hidden" name="id" value="{{Util::get("id")}}">
			@else
				<input type="hidden" name="action" value="cu">
			@endif

			{{-- Added stack if child --}}
			@if (Util::get("stack"))
				<input type="hidden" name="stack" value="{{Util::get("stack")}}">
			@endif
			

			
			{{-- 	Added foreign keys to the form for children --}}
			{{-- 	If foreign key is select do not hide 	--}}

			@if (Util::get("stack"))
				<?php $column = $columns[Util::foreignKeyString(Util::stackParentName())]; ?>
				@if (array_key_exists("select", $column))
					<div class="form-horizontal pull-right">
					<div class="control-group">
				    <label class="control-label">{{Util::cleanName(Util::stackParentName())}} </label>
				    <div class="controls">
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
				    </div>
  				</div>
  				</div>
				@else
					<input type="hidden" name="{{Util::foreignKeyString(Util::stackParentName())}}" value="{{Util::stackParentId()}}">
				@endif

				@if (Util::get("oneToOne"))
					<input type="hidden" name="oneToOne" value="{{Util::stackParentName()}}">
				@endif
			@endif



			{{-- Iterate through all columns and display correlating form input  --}}

			@foreach ($columns as $column)

			{{-- 	Added foreign keys to the form for top level parent --}}

			@if (!Util::get("stack") && Util::isForeignKey($column['field']))
				<input type="hidden" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}">
			@endif

			{{-- Hide certain fields. Laravel create and updated flags. Foreign keys. Infuse logins fields.  --}}

			@if ($column['field'] != "created_at" && $column['field'] != "updated_at" && !Util::isForeignKey($column['field'])  && Util::checkInfuseLoginFields($infuseLogin, $column) )

			<tr>
				{{-- Column Names  --}}
				@if (array_key_exists($column['field'], $header['columnNames']))
					<th>{{$header['columnNames']["{$column['field']}"]}}</th>
				@else
					<th>{{Util::cleanName($column['field'])}}</th>
				@endif

				{{-- Column Values/Form Input  --}}
				<td> 
				{{-- ckeditor  --}}
				@if (array_key_exists("ckeditor", $column))
					<textarea class="ckeditor" name="{{$column['field']}}">{{$entries->{$column['field']} }}</textarea>

				{{-- select  --}}
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

				{{-- multi select  --}}
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

				{{-- upload  --}}
				@elseif (array_key_exists("upload", $column))

					<div class="uploadArea">

					<div>
						@if (!empty($entries->{$column['field']}))
						<label class="checkbox">
				      <input type="checkbox" name="{{$column['field']}}_delete"> delete upload
				    </label>
				    @endif 
						<input type="file" name="{{$column['field']}}" class="{{(($column['upload']['imageCrop'])? "imagePreviewCropOn": "" )}}  importReplace{{$column['field']}}" id="upload{{$column['field']}}" >
					</div>
					
					
					@if (!empty($entries->{$column['field']}))

						
						
						@if ($entries->{$column['field']} != "" && preg_match('/(\.jpg|\.png|\.gif|\.JPG|\.PNG|\.GIF)$/', $entries->{$column['field']} ))
							<button type="button" class="btn btn-mini btn-link" data-toggle="modal" data-target="#{{"Modal".$column['field'].$entries->id}}">
								preview current
							</button>

							<div id="{{"Modal".$column['field'].$entries->id}}" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							  <div class="modal-header">
							    <h3 id="myModalLabel">{{$entries->{$column['field']} }}</h3>
							  </div>
							  <div class="modal-body">
							    <img src="{{$entries->url($column['field'])}}">
							  </div>
							  <div class="modal-footer">
							  	<button class="btn altColor btn-info" data-dismiss="modal" aria-hidden="true">Close</button>
							  </div>
							</div>
						@else
							<a href="<?php echo $entries->url($column['field']); ?>">current: {{$entries->{$column['field']} }}</a>
						@endif
						
					@endif

					

					@if ($column['upload']['imageCrop'])

						<input type="hidden" name="{{$column['field']}}" id="{{"croppic".$column['field'].$entries->id}}CroppedImage" value="" >
						
						<button type="button" class="btn btn-mini btn-link imageCrop" data-id="{{"croppic".$column['field'].$entries->id}}" data-path="/{{\Request::path()}}" data-width="{{$column['upload']['imageCrop']['width']}}" data-height="{{$column['upload']['imageCrop']['height']}}">
							crop upload
						</button>
						
							
						<div class="imagePreviewCrop" id="{{"croppic".$column['field'].$entries->id}}">
							<!-- image preview area-->
						</div>

					@endif

					{{-- file errors  --}}
					@foreach ($column['upload']['validations'] as $val)
						@if ($fileErrors && array_key_exists($column['field'], $fileErrors) && $val[1] == $fileErrors["{$column['field']}"])
							<?php unset($fileErrors["{$column['field']}"]); ?>
							</br><span class="label label-important">{{$val[1]}}</span>
						@else
						</br><span class="label label-info">{{$val[1]}}</span>
						@endif
					@endforeach

					</div> <!-- end of uploadArea -->


				{{-- other inputs based on column type  --}}
				@else
				
					<?php switch ($column['type']):
							case 'varchar': ?>
							<input type="text" name="{{$column['field']}}" class="importReplace{{$column['field']}}" value="{{$entries->{$column['field']} }}" {{Util::readOnly($column)}}>
					<?php 	break;
							case 'text': ?>
							<textarea name="{{$column['field']}}" class="importReplace{{$column['field']}}" {{Util::readOnly($column)}} >{{$entries->{$column['field']} }}</textarea>
					<?php 	break;
							case 'datetime':
							case 'timestamp': ?>
							<input type="text" class="selectedDateTime" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}" {{Util::readOnly($column)}} />
					<?php 	break; 
							case 'date': ?>
							<input type="text" class="selectedDate" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}" {{Util::readOnly($column)}} />
					<?php 	break;
							case 'int': ?>
							<input type="text" name="{{$column['field']}}" class="importReplace{{$column['field']}}" pattern="\d+" value="{{$entries->{$column['field']} }}" {{Util::readOnly($column)}} />
					<?php 	break;
							case 'tinyint': ?> 
							<select name="{{$column['field']}}" {{Util::readOnly($column)}}>
								<option value="0" {{($entries->{$column['field']} == 0)? 'selected="selected"' : ""}} >No</option>
								<option value="1" {{($entries->{$column['field']} == 1)? 'selected="selected"' : ""}} >Yes</option>
							</select>
					<?php break;
							default: ?>
								<input type="text" name="{{$column['field']}}" class="importReplace{{$column['field']}}" value="{{$entries->{$column['field']} }}" {{Util::readOnly($column)}} />
					<?php		
						endswitch;
					?>

				@endif
				
				{{-- Add column description  --}}
				@if (array_key_exists("description", $column))
					</br><span class="label label-info">{{$column['description']}}</span>
				@endif
				
				{{-- Display validation errors --}}
				@if ($errors && $errors->has("{$column['field']}"))
						@foreach ($errors->get("{$column['field']}") as $err)
							</br><span class="label label-important">{{$err}}</span>
						@endforeach
				@endif

				{{-- Display file errors --}}
				@if ($fileErrors && array_key_exists($column['field'], $fileErrors) && count($fileErrors) > 0)
					@foreach ($fileErrors as $err)
							</br><span class="label label-important">{{$err}}</span>
					@endforeach
				@endif




				</td>
			</tr>
			@endif
			@endforeach


			{{-- Display controls --}}
			<tr>
				<td>
					@if (Util::get("stack"))
					<div class="btn-group">
				    <a class="btn btn-small childBackLink" href="{{Util::childBackLink()}}">Back</a>
				  	@if (isset($header['edit']))
				  	<a class="btn btn-small" href="{{Util::childActionLink(Util::get("stack"), 's', $entries->id)}}">Show</a>
					  	@if(!$header['onlyOne'] && $header['deleteAction'])
							<a class="btn btn-small" href="{{Util::childActionLink(Util::get("stack"), 'd', $entries->id)}}" onclick="return confirm('Confirm delete?');">Delete</a>
							@endif
						@endif
				  </div>
					@else
					<div class="btn-group">
				    <a class="btn btn-small" href="?action=l">List</a>
				  	@if (isset($header['edit']))
				  	<a class="btn btn-small" href="?action=s&id={{$entries->id}}">Show</a>
					  	@if(!$header['onlyOne'] && $header['deleteAction'])
							<a class="btn btn-small" href="?action=d&id={{$entries->id}}" onclick="return confirm('Confirm delete?');">Delete</a>
							@endif
						@endif
						@if($infuseLogin)
						<a class="btn btn-small" href="?action=rrpp&id={{$entries->id}}">Send Reset</a>
						@endif
				  </div>
					@endif
					
				</td>
				<td> 
					<input type="submit" value="save" class="btn submitButton">
				</td>
			</tr>

			<div class="actionFixedNav">
					@if (Util::get("stack"))
				    <a class="" href="{{Util::childBackLink()}}">Back</a>
				  	@if (isset($header['edit']))
				  		<a class="" href="{{Util::childActionLink(Util::get("stack"), 's', $entries->id)}}">Show</a>
					  	@if(!$header['onlyOne'] && $header['deleteAction'])
							<a class="" href="{{Util::childActionLink(Util::get("stack"), 'd', $entries->id)}}" onclick="return confirm('Confirm delete?');">Delete</a>
							@endif
						@endif
					@else
				    <a class="" href="?action=l">List</a>
				  	@if (isset($header['edit']))
					  	<a class="" href="?action=s&id={{$entries->id}}">Show</a>
					  	@if(!$header['onlyOne'] && $header['deleteAction'])
							<a class="" href="?action=d&id={{$entries->id}}" onclick="return confirm('Confirm delete?');">Delete</a>
							@endif
						@endif
						@if($infuseLogin)
						<a class="" href="?action=rrpp&id={{$entries->id}}">Send Reset</a>
						@endif
					@endif

					<input type="submit" value="save" class="">
			</div>

			{{-- Relationship subviews --}}

			{{-- many to many needs to be in the main form --}}
			@include('infuse::scaffold._many_to_many')

			</form>

			@include('infuse::scaffold._has_one')

			@include('infuse::scaffold._children')

			
			
			
	</table>
</div>

</div>