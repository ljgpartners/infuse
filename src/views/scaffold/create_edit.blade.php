<div class="infuseInner">

<div class="infuseScaffold">
	
	<div class="page-header">
	  <h1>{{$header['name']}} <small> {{$header['description']}}</small></h1>

	  @include('infuse::scaffold._breadcrumbs')
	</div>
	
	<?php $errors = Util::flashArray("errors"); ?>
	<?php $fileErrors = Util::flashArray("file_errors"); ?>

	{{Util::fuseAlerts(Util::flash())}} 

	@include('infuse::scaffold._import_from_models')


	<table class="table table-striped table-bordered editCreateForm"> 
			<form method="post" action="?" enctype="multipart/form-data">

			{{-- Tells infuse what type of save (save, save & return, save & create another)--}}
			<input type="hidden" id="typeSubmit" name="typeSubmit" value="save">

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
			
			@if (Util::get("stack"))
				<input type="hidden" name="{{Util::foreignKeyString(Util::stackParentName())}}" value="{{Util::stackParentId()}}">
				@if (Util::get("oneToOne"))
					<input type="hidden" name="oneToOne" value="{{Util::stackParentName()}}">
				@endif
			@endif


			{{-- Iterate through all columns and display correlating form input  --}}
			
			@foreach ($columns as $column)

			{{-- 	If foreign key is select reveal on top	--}}

			@if (Util::isForeignKey($column['field']) && array_key_exists("select", $column))
				<div class="form-horizontal pull-right">
				<div class="control-group">
			    <label class="control-label">{{Util::foreignKeyStringToCleanName($column['field'])}} </label>
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
			@endif
			

			{{-- 	Added foreign keys to the form for top level parent --}}

			@if (!Util::get("stack") && Util::isForeignKey($column['field']) && !array_key_exists("select", $column))
				<input type="hidden" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}">
			@endif

			{{-- Hide certain fields. Laravel create and updated flags. Foreign keys. Infuse logins fields.  --}}

			@if (!Util::isForeignKey($column['field'])  && Util::checkInfuseLoginFields($infuseLogin, $column) )

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
					<textarea class="infuseCkeditor" data-config="{{Util::classToString($entries)."_".$column['field']}}" name="{{$column['field']}}">{{$entries->{$column['field']} }}</textarea>

				{{-- select  --}}
				@elseif (array_key_exists("select", $column))

					{{-- do regular select  --}}
					@if (!array_key_exists("nested", $column))


					<select name="{{$column['field']}}" class="importReplace{{$column['field']}}">
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


					{{-- do nested select  --}}
					@else  

						<?php  
						$totalNested = count($column['nested']);
						$nestedCount = 0;
						$selectID = "{$column['field']}_{$nestedCount}";
						$foreignKey = Util::getForeignKeyString($entries);
						?>

						@foreach ($column['nested'] as $index => $nestedModelName)
							<?php 

								if (is_array($nestedModelName)) {
									$nestedModelName = $nestedModelName['model'];
								}
								$nestColumn = null;
								if (isset($column['nested'][$index+1]) && is_array($column['nested'][$index+1])) {
									$nextModel = $column['nested'][$index+1]['model'];
									$nestColumn = $column['nested'][$index+1]['column'];
								} else {
									$nextModel = (isset($column['nested'][$index+1]))? $column['nested'][$index+1] : "";
								}
								
								
								$nestedCount++; 
								$foreignKey = ($nestedCount == $totalNested)? $column['field'] : Util::createForeignKeyString($nestedModelName);
								$cssClass = ($nestedCount == $totalNested)? "importReplace".$column['field'] : "importRemove".$column['field'];
								$selectID = "{$column['field']}_{$nestedCount}";
								$tempNextCount = $nestedCount+1;
								$selectNextID = "{$column['field']}_{$tempNextCount}";
								$overideForeignKey = (isset($column['nested'][$index]['foreign_key']))? "&overide_foreign_key=".$column['nested'][$index]['foreign_key'] : "";
								
								if (isset($column['nested'][$index+1]['not_column'])) {
									$notColumn = $column['nested'][$index+1]['not_column'];
									$notColumn = "&not_column=".key($notColumn).",".current($notColumn);
								} else {
									$notColumn = "";
								}
								
								$nestColumn = (isset($nestColumn))? "&column={$nestColumn}" : "";
							?>
							<select id="{{$selectID}}" class="{{$cssClass}}" name="{{$foreignKey}}">
							  <option value="">--</option>
							  @if ($nestedCount == 1)
							  	@foreach ($column['select'] as $value)
										<?php $columnName = end($value); ?>
										<option value="{{$value["id"]}}">{{$columnName}}</option>
									@endforeach
							  @endif

							  @if (isset($column['nested_last_array']) && !empty($entries->{$column['field']}) && $nestedCount == $totalNested)
							  	<?php $nestedLastArray = $column['nested_last_array']; ?>
							  	@foreach ($nestedLastArray as $value)
										<?php $columnName = end($value); ?>
										@if ($value['id'] == $entries->{$column['field']})
											<option selected='selected' value="{{$value["id"]}}">{{$columnName}}</option>
										@endif
									@endforeach
							  @endif
							</select>
							
							@if ($nestedCount != $totalNested)
							<script type="text/javascript">
							$(document).ready(function() {
								$("#{{$selectNextID}}").remoteChained({
								  parents : "#{{$selectID}}",
								  url : "?action=nested_select_batch&model={{$nextModel}}&foreign_key={{$foreignKey}}{{$nestColumn}}{{$overideForeignKey}}{{$notColumn}}",
								  clear : true,
    							loading : "Loading..."
								});
							});
							</script>
							@endif

							<?php $foreignKey = Util::createForeignKeyString($nestedModelName); ?>
						@endforeach

						

					@endif
					

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
					<input class="multiSelect{{$column['field']}}" name="{{$column['field']}}" type="hidden" value="{{$entries->{$column['field']} }}">

				{{-- display order  --}}
				@elseif (array_key_exists("display_order", $column))

					<input type="text" name="{{$column['field']}}" class="importReplace{{$column['field']}}" pattern="\d+" value="{{$entries->{$column['field']} }}" readonly="readonly" />

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
							
							<div id="{{"Modal".$column['field'].$entries->id}}" class="modal hide fade previewModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							  <div class="modal-header">
							    <h3 id="myModalLabel">{{$entries->{$column['field']} }}</h3>
							  </div>
							  <div class="modal-body">
							    <img class="uploadAreaPreviewImage" src="{{$entries->url($column['field'])}}">
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
								<input type="text" name="{{$column['field']}}" class="importReplace{{$column['field']}}" value="{{htmlspecialchars($entries->{$column['field']}) }}" {{Util::readOnly($column)}}>
					<?php 	break;
							case 'text': ?>
								<textarea name="{{$column['field']}}" class="importReplace{{$column['field']}}" {{Util::readOnly($column)}} >{{$entries->{$column['field']} }}</textarea>
					<?php 	break;
							case 'datetime':
							case 'timestamp': ?>
								@if ($column['field'] == "created_at" || $column['field'] == "updated_at")
									@if (Util::get("action") == 'c')
										<input type="text" name="{{$column['field']}}" value="" disabled="disabled" />
									@else
										<input type="text" name="{{$column['field']}}" value="{{$entries->{$column['field']}->tz(\Config::get('app.timezone'))->format($header['formatLaravelTimestamp'])}}" disabled="disabled" />
									@endif
								@else
									<input type="text" class="selectedDateTime" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}" {{Util::readOnly($column)}} />
								@endif
								
					<?php 	break; 
							case 'date': ?>
								<input type="text" class="selectedDate" name="{{$column['field']}}" value="{{$entries->{$column['field']} }}" {{Util::readOnly($column)}} />
					<?php 	break;
							case 'int': ?>
								<input type="text" name="{{$column['field']}}" class="importReplace{{$column['field']}}" pattern="\d+" value="{{$entries->{$column['field']} }}" {{Util::readOnly($column)}} />
					<?php 	break;
							case 'tinyint': ?> 
								<select name="{{$column['field']}}" {{Util::readOnlyWithDisabled($column)}}>
									<option value="0" {{($entries->{$column['field']} == 0)? 'selected="selected"' : ""}} >No</option>
									<option value="1" {{($entries->{$column['field']} == 1)? 'selected="selected"' : ""}} >Yes</option>
								</select>
							@if (isset($column['readOnly']))
								<input type="hidden" value="{{$entries->{$column['field']} }}" name="{{$column['field']}}">
							@endif
					<?php break;
							default: ?>

								<input type="text" name="{{$column['field']}}" class="importReplace{{$column['field']}}" value="{{htmlspecialchars($entries->{$column['field']}) }}" {{Util::readOnly($column)}} />
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

						<div class="btn-group originalBtnGroup">
					    <a class="btn btn-small childBackLink" href="{{Util::childBackLink()}}">Back</a>
					  	@if (isset($header['edit']))
					  	<a class="btn btn-small" href="{{Util::childActionLink(Util::get("stack"), 's', $entries->id)}}">Show</a>
						  	@if(!$header['onlyOne'] && $header['deleteAction'])
								<a class="btn btn-small" href="{{Util::childActionLink(Util::get("stack"), 'd', $entries->id)}}" onclick="return confirm('Confirm delete?');">Delete</a>
								@endif
							@endif
					  </div>

					  @if(count($header['callFunctions']) > 0)
					  	<div class="btn-group callFunctionsGroup">
								@foreach ($header['callFunctions'] as $function)
									<a class="btn btn-small" {{((isset($function['target']))? 'target="'.$function['target'] .'"' : "" )}} 
										href="?action=cf&id={{$entries->id}}&cf={{$function["function"]}}" 
										@if (isset($function['long_process']))
											onclick='Infuse.confirmAndblockUI("{{$function["display_name"]}}", "{{$function["function"]}}");'>
										@else
											onclick="return confirm('Confirm {{$function["display_name"]}}?');">
										@endif
										{{$function["display_name"]}}
									</a>
									@if (isset($function['long_process']))
									<span class="hide {{$function["function"]}}">
										<h4>{{$function['long_process']}}</h4>
										<div>
											<img width="32" height="32"  src="/packages/bpez/infuse/images/loading.gif" alt=""/>
										</div>
										</br>
									</span>
									@endif
								@endforeach
					  	</div>
					  @endif

					@else

						<div class="btn-group originalBtnGroup">
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

					  @if(count($header['callFunctions']) > 0)
					  	<div class="btn-group callFunctionsGroup">
								@foreach ($header['callFunctions'] as $function)
									<a class="btn btn-small" {{((isset($function['target']))? 'target="'.$function['target'] .'"' : "" )}} 
										href="?action=cf&id={{$entries->id}}&cf={{$function["function"]}}" 
										@if (isset($function['long_process']))
											onclick='Infuse.confirmAndblockUI("{{$function["display_name"]}}", "{{$function["function"]}}");'>
										@else
											onclick="return confirm('Confirm {{$function["display_name"]}}?');">
										@endif
										{{$function["display_name"]}}
									</a>
									@if (isset($function['long_process']))
									<span class="hide {{$function["function"]}}">
										<h4>{{$function['long_process']}}</h4>
										<div>
											<img width="32" height="32"  src="/packages/bpez/infuse/images/loading.gif" alt=""/>
										</div>
										</br>
									</span>
									@endif
								@endforeach
							</div>
						@endif

					@endif
					
				</td>
				<td> 
					<input type="submit" value="save" data-type-submit="save" class="btn submitButton saveSubmitButton">
					<input type="submit" value="save & return" data-type-submit="save_and_return" class="btn submitButton saveSubmitButton">
					@if (Util::get("action") == "c")
					<input type="submit" value="save & create another" data-type-submit="save_and_create_another" class="btn submitButton saveSubmitButton">
					@endif
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


					@if(count($header['callFunctions']) > 0)
						@foreach ($header['callFunctions'] as $function)
							<a class="" {{((isset($function['target']))? 'target="'.$function['target'] .'"' : "" )}} 
									href="?action=cf&id={{$entries->id}}&cf={{$function["function"]}}" 
									@if (isset($function['long_process']))
										onclick='Infuse.confirmAndblockUI("{{$function["display_name"]}}", "{{$function["function"]}}");'>
									@else
										onclick="return confirm('Confirm {{$function["display_name"]}}?');">
									@endif
									{{$function["display_name"]}}
							</a>
							@if (isset($function['long_process']))
							<div class="hide {{$function["function"]}}">
								<h4>{{$function['long_process']}}</h4>
								<div>
									<img width="32" height="32"  src="/packages/bpez/infuse/images/loading.gif" alt=""/>
								</div>
								</br>
							</div>
							@endif
						@endforeach
					@endif
					

					<div class="submitGroup">
						<input type="submit" value="save" data-type-submit="save" class="saveSubmitButton">
						<input type="submit" value="save & return" data-type-submit="save_and_return" class="saveSubmitButton">
						@if (Util::get("action") == "c")
						<input type="submit" value="save & create another" data-type-submit="save_and_create_another" class="saveSubmitButton">
						@endif
					</div>
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