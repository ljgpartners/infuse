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


<div class="container-fluid createEditTemplate">

	<div class="row">
		<div class="col-sm-12 col-xs-12">
			<?php $errors = Util::flashArray("errors"); ?>
			<?php $fileErrors = Util::flashArray("file_errors"); ?>
			{!!Util::fuseAlerts(Util::flash())!!}
		</div>
	</div>


	<div class="row">
		<div class="col-sm-12 col-xs-12">
			@include('infuse::scaffold._import_from_models')
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12 col-xs-12">


		<form method="post" action="?" enctype="multipart/form-data"  class="form-horizontal" role="form">

		{{-- Laravel csrf token --}}
		<input type="hidden" name="_token" value="{!! csrf_token() !!}" />

		{{-- Tells infuse what type of save (save, save & edit, save & create another)--}}
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

		{{-- 	Check if column is actually an hstore column	--}}

		<?php $columnValue = Util::getColumnValue($entries, $column); ?>

		{{-- 	If foreign key is select reveal on top	--}}

		@if (Util::isForeignKey($column['field']) && array_key_exists("select", $column))
			<div class="form-horizontal pull-right">
			<div class="form-group">
		    <label class="control-label">{{Util::foreignKeyStringToCleanName($column['field'])}} </label>
		    <div class="controls">
		      <select name="{{$column['field']}}" class="form-control">
						@if (array_key_exists("select_blank", $column))
							<option value=""></option>
						@endif
						@foreach ($column['select'] as $value)
								<?php $columnName = end($value); ?>
								@if ($columnValue == $value["id"])
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
			<input type="hidden" name="{{$column['field']}}" value="{{$columnValue }}">
		@endif

		{{-- Hide certain fields. Laravel create and updated flags. Foreign keys. Infuse logins fields.  --}}

		@if (!Util::isForeignKey($column['field'])  && Util::checkInfuseLoginFields($infuseLogin, $column) )

		<div class="form-group">
			<div class="col-sm-12 col-xs-12">
				<div class="input-group">


			{{-- Column Names  --}}
			<?php $masterColumnName = (array_key_exists($column['field'], $header['columnNames']))? $header['columnNames']["{$column['field']}"] : Util::cleanName($column['field']); ?>

			@if (!array_key_exists("upload", $column))
				<span class="input-group-addon">{{Util::cleanName($column['field'])}}</span>
			@endif

			{{-- Column Values/Form Input  --}}

			{{-- ckeditor  --}}
			@if (array_key_exists("ckeditor", $column))
				<textarea class="infuseCkeditor" data-config="{{Util::classToString($entries)."_".$column['field']}}" name="{{$column['field']}}">{{$columnValue }}</textarea>

			{{-- select  --}}
			@elseif (array_key_exists("select", $column))

				{{-- do regular select  --}}
				@if (!array_key_exists("nested", $column))


				<select name="{{$column['field']}}" class="importReplace{{$column['field']}} form-control">
					@if (array_key_exists("select_blank", $column))
						<option value=""></option>
					@endif
					@foreach ($column['select'] as $value)
							<?php $columnName = end($value); ?>
							@if ($columnValue == $value["id"])
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
						<select id="{{$selectID}}" class="{{$cssClass}} form-control" name="{{$foreignKey}}">
						  <option value="">--</option>
						  @if ($nestedCount == 1)
						  	@foreach ($column['select'] as $value)
									<?php $columnName = end($value); ?>
									<option value="{{$value["id"]}}">{{$columnName}}</option>
								@endforeach
						  @endif

						  @if (isset($column['nested_last_array']) && !empty($columnValue) && $nestedCount == $totalNested)
						  	<?php $nestedLastArray = $column['nested_last_array']; ?>
						  	@foreach ($nestedLastArray as $value)
									<?php $columnName = end($value); ?>
									@if ($value['id'] == $columnValue)
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
							  url : "?action=nested_select_batch&model={!! $nextModel !!}&foreign_key={!! $foreignKey !!}{!! $nestColumn !!}{!! $overideForeignKey !!}{!! $notColumn !!}",
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
							array_push($dataMultiSelect, array("id" => (int)$value["id"], "value" => $columnName));
					endforeach;
				?>

				<div class="multiSelect"  data-name="{{$column['field']}}" data-data='{{json_encode($dataMultiSelect)}}' data-value="{{$columnValue }}"></div>
				<input class="multiSelect{{$column['field']}}" name="{{$column['field']}}" type="hidden" value="{{$columnValue }}">



			{{-- display order  --}}
			@elseif (array_key_exists("display_order", $column))

				<input type="text" name="{{$column['field']}}" class="importReplace{{$column['field']}} form-control" pattern="\d+" value="{{$columnValue }}" readonly="readonly" />

			{{-- upload  --}}
			@elseif (array_key_exists("upload", $column))

				<!--<input type="file" name="{{$column['field']}}" class="{{(($column['upload']['imageCrop'])? "imagePreviewCropOn": "" )}}  importReplace{{$column['field']}}" id="upload{{$column['field']}}" >-->

				<div class="input-group-btn">
		          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">{{$masterColumnName}} <span class="caret"></span></button>
		          @if (!empty($columnValue))
			        <ul class="dropdown-menu dropdown-menu-form" role="menu">
			        	<li>
			        		@if (preg_match('/(\.jpg|\.png|\.gif|\.JPG|\.PNG|\.GIF)$/', $columnValue ))
			        			<a href="" data-toggle="modal" data-target="#{{"Modal".$column['field'].$entries->id}}">Preview current</a>
			        		@else
								<a href="<?php echo $entries->url($column['field']); ?>" >Current {{$columnValue}}</a>
							@endif
			        	</li>
			          <li>
			          	<label class="checkbox">
		                Delete upload
		                <input type="checkbox" name="{{$column['field']}}_delete">
		            	</label>
			          </li>
			        </ul>
			        @endif
		        </div>
		        <input class="form-control" type="text" readonly="readonly" value="{{ (!empty($columnValue))? $columnValue : "" }}">
		        <div class="input-group-btn">
		        	<span class="btn btn-default btn-file"  tabindex="-1">
		              Browse… <input multiple="" type="file" name="{{$column['field']}}" class="{{(($column['upload']['imageCrop'])? "imagePreviewCropOn": "" )}}  importReplace{{$column['field']}}" id="upload{{$column['field']}}">
		          </span>
		        </div>



				@if (!empty($columnValue))
					@if (preg_match('/(\.jpg|\.png|\.gif|\.JPG|\.PNG|\.GIF)$/', $columnValue ))
						<div id="{{"Modal".$column['field'].$entries->id}}" class="modal fade previewModal"  tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
						  <div class="modal-dialog modal-lg">
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						        <h4 class="modal-title">{{$columnValue }}</h4>
						      </div>
						      <div class="modal-body">
						        <img class="uploadAreaPreviewImage" src="{{$entries->url($column['field'])}}">
						      </div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						      </div>
						    </div><!-- /.modal-content -->
						  </div><!-- /.modal-dialog -->
						</div><!-- /.modal -->
					@endif
				@endif



				@if ($column['upload']['imageCrop'])

					<input type="hidden" name="{{$column['field']}}" id="{{"croppic".$column['field'].$entries->id}}CroppedImage" value="" >

					<button type="button" class="btn btn-default btn-xs btn-link imageCrop" data-id="{{"croppic".$column['field'].$entries->id}}" data-path="/{{\Request::path()}}" data-width="{{$column['upload']['imageCrop']['width']}}" data-height="{{$column['upload']['imageCrop']['height']}}">
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
						<span class="label label-danger">{{$val[1]}}</span>
					@else
						<span class="label label-default-bryan">{{$val[1]}}</span>
					@endif
				@endforeach


			{{-- other inputs based on column type  --}}
			@else


				<?php switch ($column['type']):
						case 'varchar': ?>
							<input type="text" maxlength="{{preg_replace("/[^0-9]/", "", $column['type_original'])}}" name="{{$column['field']}}" class="importReplace{{$column['field']}} form-control" value="{{htmlspecialchars($columnValue) }}" {{Util::readOnly($column)}}>
				<?php 	break;
						case 'text': ?>
							<textarea name="{{$column['field']}}" class="importReplace{{$column['field']}} form-control" {{Util::readOnly($column)}} >{{$columnValue }}</textarea>
				<?php 	break;
						case 'datetime':
						case 'timestamp': ?>
							@if ($column['field'] == "created_at" || $column['field'] == "updated_at")
								@if (Util::get("action") == 'c')
									<input type="text" name="{{$column['field']}}" value="" disabled="disabled" class="form-control" />
								@else
									<input type="text" name="{{$column['field']}}" value="{{$columnValue->tz(\Config::get('app.timezone'))->format($header['formatLaravelTimestamp'])}}" disabled="disabled"  class="form-control"/>
								@endif
							@else
								<input type="text" class="selectedDateTime form-control" name="{{$column['field']}}" value="{{ (empty($columnValue))? (new \DateTime())->format('Y-m-d H:i:s') : $columnValue }}" {{Util::readOnly($column)}} />
							@endif <!-- (empty($columnValue))? "0000-00-00 00:00:00" : $columnValue -->

				<?php 	break;
						case 'date': ?>
							<input type="text" class="selectedDate form-control" name="{{$column['field']}}" value="{{ (empty($columnValue))? (new \DateTime())->format('Y-m-d') : $columnValue }}" {{Util::readOnly($column)}} />
				<?php 	break;
						case 'int': ?>
							<input type="number" name="{{$column['field']}}" class="importReplace{{$column['field']}} form-control" pattern="\d+" value="{{ (empty($columnValue))? 0 : $columnValue  }}" {{Util::readOnly($column)}} />
				<?php 	break;
						case 'float': ?>
									<input type="number" name="{{$column['field']}}" class="importReplace{{$column['field']}} form-control" step="any" value="{{ (empty($columnValue))? 0 : $columnValue  }}" {{Util::readOnly($column)}} />
				<?php 	break;
						case 'tinyint': ?>
							<select name="{{$column['field']}}" {{Util::readOnlyWithDisabled($column)}} class="form-control">
								<option value="0" {{($columnValue == 0)? 'selected="selected"' : ""}} >No</option>
								<option value="1" {{($columnValue == 1)? 'selected="selected"' : ""}} >Yes</option>
							</select>
						@if (isset($column['readOnly']))
							<input type="hidden" value="{{$columnValue }}" name="{{$column['field']}}">
						@endif
				<?php break;
						default: ?>

							<input type="text" name="{{$column['field']}}" class="importReplace{{$column['field']}} form-control" value="{{htmlspecialchars($columnValue) }}" {{Util::readOnly($column)}} />
				<?php
					endswitch;
				?>

			@endif

				</div> <!-- end of input-group -->

				<div class="infuseLabels">
					{{-- Add column description  --}}
					@if (array_key_exists("description", $column))
						@if (array_key_exists("description_popover", $column))
							<a href="#" tabindex="0" class="infoPopOver" role="button" data-toggle="popover" data-html="true" data-trigger="focus" title="Information" data-content="{{$column['description']}}">Information</a>
						@else
							<span class="label label-default-bryan">{{$column['description']}}</span>
						@endif
					@endif


					{{-- Display validation errors --}}
					@if ($errors && $errors->has("{$column['field']}"))
						@foreach ($errors->get("{$column['field']}") as $err)
							<span class="label label-danger">{{$err}}</span>
						@endforeach
					@endif

					{{-- Display file errors --}}
					@if ($fileErrors && array_key_exists($column['field'], $fileErrors) && count($fileErrors) > 0)
						@foreach ($fileErrors as $err)
								<span class="label label-danger">{{$err}}</span>
						@endforeach
					@endif
				</div>

			</div> <!-- end of col-sm-12 col-xs-12 -->
		</div> <!-- end of form-group -->
		@endif
		@endforeach


		<nav class="bottom">
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
					<input type="submit" value="save & edit" data-type-submit="save_and_return" class="saveSubmitButton">
					@if (Util::get("action") == "c")
					<input type="submit" value="save & create another" data-type-submit="save_and_create_another" class="saveSubmitButton">
					@endif
				</div>
		</nav>

		{{-- Relationship subviews --}}

		{{-- many to many needs to be in the main form --}}
		@include('infuse::scaffold._many_to_many')

		</form>

		</div>
	</div>


	@include('infuse::scaffold._has_one')

	@include('infuse::scaffold._children')


</div>
