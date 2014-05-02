<?php 
if (isset($header['filters'])):
		$individualFilters = "";
		for($x=1; $x <= count($header['filters']); $x++)
			$individualFilters .= "&filter_".$x."=".Util::get("filter_".$x);
		$filters = "&action=f&filter_count=".count($header['filters']).$individualFilters; 
else:
	$filters = "";
endif;

$modelInstanceForPermissionCheck = $entries->first();
?>

<div class="infuseInner">

<div class="infuseScaffold">

	<div class="page-header">
	  <h1>{{$header['name']}} <small> {{$header['description']}}</small></h1>
	</div>

	{{Util::fuseAlerts(Util::flash())}}

	@if(!$header['onlyOne'])
	<div class="infuseTopButtonGroup"> 
		@if (Util::checkPermission($user, $modelInstanceForPermissionCheck, "create"))
		<div class="btn-group">
			<a class="btn mainColor" href="?action=c">Create {{$header['name']}}</a>
		</div>
		@endif
    <div class="btn-group">
      <button class="btn altColor btn-info  dropdown-toggle" data-toggle="dropdown">Add Filter <span class="caret"></span></button>
      <ul class="dropdown-menu filtersDropDown">
      	@foreach ($columns as $column)
      			@if (!Util::isForeignKey($column['field']))
							<li><a href="" class="filterColumn filter{{$column['field']}}" data-filter-column="{{$column['field']}}">{{Util::cleanName($column['field'])}}</a></li>
						@endif
				@endforeach
      </ul>
    </div>
    <div class="btn-group">
      <button class="btn altColor btn-info  dropdown-toggle" data-toggle="dropdown">Other Actions <span class="caret"></span></button>
      <ul class="dropdown-menu">
        <li><a target="_BLANK" class="downloadCSVLink" href='?action=l&pg=a&toCSV=1' data-filter-download='?action=f&pg=a&toCSV=1{{$filters}}'>Download CSV</a></li>

        @foreach ($header['addOtherActions'] as $action)
        	<li><a {{((isset($action['target']))? 'target="'.$action['target'] .'"' : "" )}} href='?action=oa&cf={{$action['function']}}'>{{$action['display_name']}}</a></li>
        @endforeach
      </ul>
    </div>
	</div>
	@endif

	<table class="table  table-bordered table-striped">

		<tr class="filtersContainer">
			<td colspan="{{count($columns)+1}}">
				<form action="?" method="post" class="filtersForm">
					<div class="btn-group">
						<input type="submit" value="Filter" class="btn btn-small btn-primary">
					</div>
					<div class="btn-group">
						<a class="btn btn-small btn-primary clearFilters">Clear Filters</a>
					</div>
					<div class="appendFilters"></div>
					<input type="hidden" value='0' name="filter_count" class="filterCount">
					<input type="hidden" value='f' name="action">
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

					@if (array_key_exists($column['field'], $header['columnNames']))
						<th>{{$header['columnNames']["{$column['field']}"]}}</th>
					@else
						<th>{{Util::cleanName($column['field'])}}</th>
					@endif
					
				@endif
			@endforeach
			<th></th>
		</tr>

		@foreach ($entries as $entry)
		<tr>
			@foreach ($columns as $column)
				@if (in_array($column['field'], $header['list']))
					<td>
					@if (array_key_exists("select", $column))

						@foreach ($column['select'] as $value)
								@if ($entry->{$column['field']} == $value["id"])
									<?php $columnName = end($value); ?>
									{{$columnName}}
								@endif
						@endforeach

					@elseif (array_key_exists("upload", $column))
						<div class="previewImage">
							<img src="{{$entry->url($column['field'])}}" alt=""> 
						</div>

					@elseif (array_key_exists("display_order", $column))
										<span>{{$entry->{$column['field']} }}</span> <span class="icon-arrow-up"></span> <span class="icon-arrow-down"></span>
					@else 
									{{(($column['type'] == "text"))? Util::truncateText($entry->{$column['field']}, "25") : $entry->{$column['field']} }}
					@endif
					</td>
				@endif
			@endforeach
			<td>
				<div class="btn-group">
				  <a class="btn btn-small dropdown-toggle" data-toggle="dropdown" href="#">
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
									<li><a href="?action=cf&id={{$entry->id}}&cf={{$function["function"]}}" onclick="return confirm('Confirm {{$function["display_name"]}}?');">{{$function["display_name"]}}</a></li>
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
				<div class="hero-unit">
				  <h1>{{$header['name']}} listing is empty.</h1>
				  <p>To create the first one click the create button below.</p>
				  <p>
				    <a href="?action=c" class="btn mainColor">
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
				<div class="hero-unit">
				  <h1>No results.</h1>
				</div>
			</td>
		</tr>
	@endif

	</table>

	@if ($header['pagination']['count'] > 0)
	<div class="pagination pagination-small pagination-centered">
	  <ul>

	  	<?php $pagination = $header['pagination']; ?>
	  	@if ($pagination['active_page'] != 1)
	  		<li><a href='?pg={{$pagination['active_page']-1}}{{$filters}}'>&laquo;</a></li>
	  	@else
	  		<li class="disabled"><a href="javascript: void(0)">&laquo;</a></li>
	  	@endif
	  	
	  	@if ($pagination['count'] > $pagination['limit'])
	  		<?php $times = ceil((int)$pagination['count']/(int)$pagination['limit']); ?>
	  		@for ($i=1; $i < $times+1; $i++)
	  			<li class="{{($pagination['active_page'] == $i)? "active" : ""}}">
	  				<a href='?pg={{$i}}{{$filters}}'>
	  					{{$i}}
	  				</a>
	  			</li>
	  	@endfor
	  	 <li><a href='?pg=a{{$filters}}'>View All</a></li>
	  	@endif
	  	
	  	@if (isset($times) && $pagination['active_page'] != $times)
	    	<li><a href='?pg={{$pagination['active_page']+1}}{{$filters}}'>&raquo;</a></li>
	    @else
	    	<li class="disabled"><a href="javascript: void(0)">&raquo;</a></li>
	   	@endif
	    
	  </ul>
	</div>
	@endif


</div>

</div>




