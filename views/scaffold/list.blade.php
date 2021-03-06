<?php
if (isset($header['filters'])):
		$individualFilters = "";
		for($x=1; $x <= count($header['filters']); $x++)
			$individualFilters .= "&filter_".$x."=".json_encode(Util::get("filter_".$x));
		$filters = "&action=f&filter_count=".count($header['filters']).$individualFilters;
else:
	$filters = "";
endif;

$modelInstanceForPermissionCheck = $entries->first();

$filterList = $header['list'];
if(($key = array_search("updated_at", $filterList)) !== false) {
    unset($filterList[$key]);
}
if (isset($header['filterList'])) {
	$filterList = (empty($header['filterList']))? $filterList : $header['filterList'];
}

$currentScope = Util::get("scope");
$currentScopeUrl = (!empty($currentScope))? "&scope=".$currentScope : "";

$displayOrder = false;
foreach ($columns as $column) {
	if (array_key_exists("display_order", $column)) {
		$displayOrder = true;
		break;
	}
}

?>


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
</div>

<div class="container-fluid">

	<div class="row">
		<div class="col-sm-12 col-xs-12">
		<div class="form-group">

		@if(!$header['onlyOne'])
		<div class="infuseTopButtonGroup">
			@if (Util::checkPermission($user, $modelInstanceForPermissionCheck, "create"))
			<div class="btn-group">
				<a class="btn btn-default mainColor" href="?action=c">Create {{$header['name']}}</a>
			</div>
			@endif
	    <div class="btn-group">
	      <button class="btn btn-default altColor  dropdown-toggle" data-toggle="dropdown">Add Filter <span class="caret"></span></button>
	      <ul class="dropdown-menu filtersDropDown">
	      	@foreach ($columns as $column)
	      			@if (!Util::isForeignKey($column['field']) && in_array($column['field'], $filterList))
								<li><a href="" class="filterColumn filter{{$column['field']}}" data-filter-column="{{$column['field']}}">{{Util::cleanName($column['field'])}}</a></li>
							@endif
					@endforeach
	      </ul>
	    </div>
	    <div class="btn-group">
	      <button class="btn btn-default altColor  dropdown-toggle" data-toggle="dropdown">Other Actions <span class="caret"></span></button>
	      <ul class="dropdown-menu">
	        <li><a target="_BLANK" class="downloadCSVLink" href='?action=l&pg=a&toCSV=1' data-filter-download='?action=f&pg=a&toCSV=1{{$filters}}'>Download CSV</a></li>

	        @foreach ($header['addOtherActions'] as $action)
	        	<li><a {{((isset($action['target']))? 'target="'.$action['target'] .'"' : "" )}} href='?action=oa&cf={{$action['function']}}'>{{$action['display_name']}}</a></li>
	        @endforeach
	      </ul>
	    </div>
		</div>
		@endif

		</div>
		</div>
	</div> <!-- end of row -->

	@if (isset($header['queryScopes']))
		<?php
			$header['queryScopes'] = array_chunk($header['queryScopes'], 6, true);
		?>
		@foreach ($header['queryScopes'] as $row)
		<div class="row">
			<div class="col-sm-12 col-xs-12">
			<div class="form-group">
			<div class="btn-group btn-group-justified" role="group">

			@foreach ($row as $displayName => $functionName)
			<div class="btn-group" role="group">
		    <a href="?action=l&scope={{$functionName}}" class="btn btn-default {{($currentScope == $functionName)? "disabled" : ""}}">{{$displayName}}</a>
		  </div>
			@endforeach

			</div>
			</div>
			</div>
		</div> <!-- end of row -->
		@endforeach
	@endif


	<div class="row">
		<div class="col-sm-12 col-xs-12">
			<div class="form-group">


			<table class="table  table-bordered table-striped">


				<tr class="filtersContainer">
					<td colspan="{{count($columns)+1}}">
						<form action="?" method="post" class="filtersForm">
							<div class="btn-group">
								<input type="submit" value="Filter" class="btn btn-sm btn-primary">
							</div>
							<div class="btn-group">
								<a class="btn btn-sm btn-primary clearFilters">Clear Filters</a>
							</div>
							<div class="appendFilters"></div>
							<input type="hidden" value='0' name="filter_count" class="filterCount">
							<input type="hidden" value='f' name="action">
							{{-- Laravel csrf token --}}
							<input type="hidden" name="_token" value="{!! csrf_token() !!}" />
						</form>


							@if (isset($header['filters']))
				  			<div class="hide rebuildFiltersThroughJs" data-filter-count="{{count($header['filters'])}}">
				  				@for($x=1; $x <= count($header['filters']); $x++)
				  					<div data-filter-{{$x}}-first="{{$header['filters'][$x-1][0]}}" data-filter-{{$x}}-first-display="{{Util::cleanName($header['filters'][$x-1][0])}}" data-filter-{{$x}}-second="{{$header['filters'][$x-1][1]}}" data-filter-{{$x}}-third="{{$header['filters'][$x-1][2]}}"></div>
				  				@endfor
				  			</div>
				  		@endif
						</div>
					</td>
				</tr>

				<tr>
					@foreach ($columns as $column)
						@if (in_array($column['field'], $header['list']))

							@if (!$displayOrder)
								@if (array_key_exists($column['field'], $header['columnNames']))
									<th><a href="?action=l&order={{$column['field']}}">{{$header['columnNames']["{$column['field']}"]}}</a></th>
								@else
									<th><a href="?action=l&order={{$column['field']}}">{{Util::cleanName($column['field'])}}</a></th>
								@endif
							@else
								@if (array_key_exists($column['field'], $header['columnNames']))
									<th>{{$header['columnNames']["{$column['field']}"]}}</th>
								@else
									<th>{{Util::cleanName($column['field'])}}</th>
								@endif
							@endif

						@endif
					@endforeach
					<th></th>
				</tr>

				@foreach ($entries as $entry)
				<tr data-class="{{get_class($entry)}}" class="{{get_class($entry)}}">
					@foreach ($columns as $column)

						<?php $columnValue = Util::getColumnValue($entry, $column); ?>

						{{-- 	Feed to url function hstore config	--}}

						<?php $hstoreColumn = (isset($column['hstore_column']) && $column['hstore_column']) ? $column['hstore_column'] : false; ?>

						@if (in_array($column['field'], $header['list']))
							<td>
							@if (array_key_exists("select", $column))
								<?php $selectArray = (array_key_exists("nested", $column) && isset($column['nested_last_array']))? $column['nested_last_array'] : $column['select'] ; ?>
								@foreach ($selectArray as $value)
										@if ($columnValue == $value["id"])
											<?php $columnName = end($value); ?>
											{{$columnName}}
										@endif
								@endforeach

							@elseif (array_key_exists("display_order", $column))
								<div class="orderColumn">
									<a class="upOrder" data-id="{{$entry->id}}" data-url="{{$_SERVER['REQUEST_URI']}}" data-column="{{$column['field']}}" data-model="{{get_class($entry)}}" href="">[up]</a>
									<a class="downOrder" data-id="{{$entry->id}}" data-url="{{$_SERVER['REQUEST_URI']}}" data-column="{{$column['field']}}" data-model="{{get_class($entry)}}" href="">[down]</a>
								</div>

							@elseif (array_key_exists("upload", $column))
								<div class="previewImage">
									<img src="{{$entry->url($column['field'], $hstoreColumn)}}" alt="">
								</div>

							@else
								@if ($column['field'] == "updated_at")
									{{$columnValue->tz(\Config::get('app.timezone'))->format($header['formatLaravelTimestamp'])}}
								@else
									{{(($column['type'] == "text"))? Util::truncateText($columnValue, "25") : $columnValue }}
								@endif

							@endif
							</td>
						@endif
					@endforeach
					<td>
						<div class="btn-group">
						  <a class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" href="#">
						    Action
						    <span class="caret"></span>
						  </a>
						  <ul class="dropdown-menu">
						    <li><a href="?action=s&id={{$entry->id}}">Show</a></li>
						    @if (Util::checkPermission($user, $modelInstanceForPermissionCheck, "update"))
								<li><a href="?action=e&id={{$entry->id}}">Edit</a></li>
								@endif
								@if(!$header['onlyOne'])
									@if (Util::checkPermission($user, $modelInstanceForPermissionCheck, "delete"))
									<li><a href="?action=d&id={{$entry->id}}" onclick="return confirm('Confirm delete?');">Delete</a></li>
									@endif
								<!--<li><a href="?action=cd&id={{$entry->id}}">Duplicate</a></li>-->
								@endif
								@if($infuseLogin)
								<li><a href="?action=rrpp&id={{$entry->id}}">Send Reset</a></li>
								@endif

								@if (Util::checkPermission($user, $modelInstanceForPermissionCheck, "update"))
									@if(count($header['callFunctions']) > 0)
										@foreach ($header['callFunctions'] as $function)
											<li>
												<a {{((isset($function['target']))? 'target="'.$function['target'] .'"' : "" )}}
													href="?action=cf&id={{$entry->id}}&cf={{$function["function"]}}"
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
														<img width="32" height="32"  src="/bpez/infuse/images/loading.gif" alt=""/>
													</div>
													</br>
												</div>
												@endif
											</li>
										@endforeach
									@endif
								@endif

						  </ul>
						</div>

					</td>
				</tr>
				@endforeach

				@if ($header['pagination']['count'] < 1 && !isset($header['filters']))
				<tr>
					<td colspan="{{count($columns)+1}}">
						<div class="jumbotron">
						  <h1>{{$header['name']}} listing is empty.</h1>
						  <p>To create the first one click the create button below.</p>
						  <p>
						    <a href="?action=c" class="btn btn-default mainColor">
						      Create {{$header['name']}}
						    </a>
						  </p>
						</div>
					</td>
				</tr>
			@endif

			@if ($header['pagination']['count'] < 1 && isset($header['filters']))
			<tr>
					<td colspan="{{count($columns)+1}}">
						<div class="jumbotron">
						  <h1>No results.</h1>
						</div>
					</td>
				</tr>
			@endif

			</table>
			</div>
			</div>
		</div>
	</div> <!-- end of row -->

	@if ($header['pagination']['count'] > 0)
	<div class="text-center">
	  <ul class="pagination pagination-sm ">

	  	<?php $pagination = $header['pagination']; ?>
	  	@if ($pagination['active_page'] != 1)
	  		<li><a href='?pg={{$pagination['active_page']-1}}{{$filters}}{{$currentScopeUrl}}'>&laquo;</a></li>
	  	@else
	  		<li class="disabled"><a href="javascript: void(0)">&laquo;</a></li>
	  	@endif

	  	@if ($pagination['count'] > $pagination['limit'])
	  		<?php $times = ceil((int)$pagination['count']/(int)$pagination['limit']); ?>
	  		@for ($i=1; $i < $times+1; $i++)
	  			<li class="{{($pagination['active_page'] == $i)? "active" : ""}}">
	  				<a href='?pg={{$i}}{{$filters}}{{$currentScopeUrl}}'>
	  					{{$i}}
	  				</a>
	  			</li>
	  	@endfor
	  	 <li><a href='?pg=a{{$filters}}'>View All</a></li>
	  	@endif

	  	@if (isset($times) && $pagination['active_page'] != $times)
	    	<li><a href='?pg={{$pagination['active_page']+1}}{{$filters}}{{$currentScopeUrl}}'>&raquo;</a></li>
	    @else
	    	<li class="disabled"><a href="javascript: void(0)">&raquo;</a></li>
	   	@endif

	  </ul>
	</div>
	@endif



</div>
