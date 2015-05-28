@if (isset($header['edit']) && count($header['associations']) > 0)
<div class="row">
	<div class="col-sm-12 col-xs-12">



	@foreach ($header['associations'] as $association)
	<?php
			$model = $association[0];
			$childTitle = $association[1];
			$childColumns = $association[2];
			$childOrderColumnIntegerBit = (isset($association[3]) && is_array($association[3]) && isset($association[3]['order_integer']) )? $association[3]['order_integer'] : false;
			$childOrderColumn = (isset($association[3]) && is_array($association[3]) && isset($association[3]['order_column']) )? $association[3]['order_column'] : false;
			$childOrderDirection = (isset($association[3]) && is_array($association[3]) && isset($association[3]['order_direction']) )? $association[3]['order_direction'] : false;
			$importCSV = (isset($association[3]) && is_array($association[3]) && isset($association[3]['import_csv']) )? $association[3]['import_csv'] : false;
			$header['deleteAction'] = (isset($association[3]) && is_array($association[3]) && isset($association[3]['delete_action']) )? $association[3]['delete_action'] : true;

			$importCSVFunction = (isset($association[3]) && is_array($association[3]) && isset($association[3]['import_csv_function']) )? $association[3]['import_csv_function'] : false;
			$importCSVFunctionUrl = (isset($association[3]) && is_array($association[3]) && isset($association[3]['import_csv_function_url']) )? $association[3]['import_csv_function_url'] : false;
			$importCSVFunctionText = (isset($association[3]) && is_array($association[3]) && isset($association[3]['import_csv_function_text']) )? $association[3]['import_csv_function_text'] : false;

			$exportCSVFunction = (isset($association[3]) && is_array($association[3]) && isset($association[3]['export_csv_function']) )? $association[3]['export_csv_function'] : false;
			$exportCSVFunctionText = (isset($association[3]) && is_array($association[3]) && isset($association[3]['export_csv_function_text']) )? $association[3]['export_csv_function_text'] : false;

			$numColumns = count($childColumns)+1;

			$split = (isset($association[3]) && is_array($association[3]) && isset($association[3]['split']) && is_array($association[3]['split']))? $association[3]['split'] : false;
	?>

	<?php

		if ($split) {

			foreach ($split as $indexTitle => $whereStatement) {
				if ($childOrderColumn && $childOrderDirection ) {
					$split[$indexTitle] = $entries->hasMany(Util::under2camel(ucfirst($model)))
						->whereRaw($whereStatement);

					if ($childOrderColumnIntegerBit) {
						$split[$indexTitle] = $split[$indexTitle]->orderBy($db::raw("{$childOrderColumn} = 0"), "asc");
					}

					$split[$indexTitle] = $split[$indexTitle]->orderBy($childOrderColumn, $childOrderDirection)
						->get();

				} else {
					$split[$indexTitle]= $entries->hasMany(Util::under2camel(ucfirst($model)))
						->whereRaw($whereStatement)
						->get();
				}
			}

		} else {
			if ($childOrderColumn && $childOrderDirection ) {
				$hasManyObject = $entries->hasMany(Util::under2camel(ucfirst($model)));

				if ($childOrderColumnIntegerBit) {
					$hasManyObject = $hasManyObject->orderBy($db::raw("{$childOrderColumn} = 0"), "asc");
				}

				$hasManyObject = $hasManyObject->orderBy($childOrderColumn, $childOrderDirection)
					->get();

			} else {
				$hasManyObject = $entries->hasMany(Util::under2camel(ucfirst($model)))
					->get();
			}
		}
	?>

	<div class="panel panel-default">
		<div class="panel-heading"><h4>{{$childTitle}}</h4></div>
		<div class="table-responsive">
		<table class="table table-striped table-bordered">

			@if (!$split)
				@include('infuse::scaffold.__children_loop')
			@else
				@foreach ($split as $indexTitle => $hasManyObject)
					<tr>
						<td colspan="{{$numColumns}}">
							<h4>{{$indexTitle}}</h4>
						</td>
					</tr>

					@include('infuse::scaffold.__children_loop')

					<tr>
						<td colspan="{{$numColumns}}">
							&#32;
						</td>
					</tr>
				@endforeach
			@endif



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
						<input type="submit" value="import" class="btn btn-default submitButton">
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
						<input type="submit" value="import" class="btn btn-default submitButton">
					</form>
				</th>
			</tr>
			@endif


			@if ($exportCSVFunction)
			<tr>
				<th colspan="{{$numColumns}}">
					<form method="POST"  enctype="multipart/form-data" target="_BLANK">
						<p>{{(($exportCSVFunctionText)? $exportCSVFunctionText : "Export all." )}}</p>
						<input type="hidden" name="action" value="ecsvc">
						<input type="hidden" name="function" value="{{$exportCSVFunction}}">
						<input type="hidden" name="back" value="{{$_SERVER['REQUEST_URI']}}">
						<input type="hidden" name="parent_id" value="{{$entries->id}}">
						<input type="submit" value="export" class="btn submitButton">
					</form>
				</th>
			</tr>
			@endif


		</table>

		</div>
	</div>
	@endforeach

	</div>
</div>
@endif
