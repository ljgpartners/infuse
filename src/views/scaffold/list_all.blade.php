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

	<table class="table  table-bordered table-striped">
		<tr>
			<td colspan="{{count($columns)+1}}">
				<div class="btn-group">
					<a class="btn btn-small btn-success" href="?action=c">Create {{$header['name']}}</a>
				</div>
        <div class="btn-group">
          <button class="btn btn-small btn-inverse dropdown-toggle" data-toggle="dropdown">Filter <span class="caret"></span></button>
          <ul class="dropdown-menu">
          	@foreach ($columns as $column)
								<li><a href="" class="filterColumn">{{Util::cleanName($column['field'])}}</a></li>
						@endforeach
          </ul>
        </div>
        <div class="btn-group">
          <button class="btn btn-small btn-inverse dropdown-toggle" data-toggle="dropdown">Other Actions <span class="caret"></span></button>
          <ul class="dropdown-menu">
            <li><a target="_BLANK" href="?action=toCSV">Download CSV</a></li>
          </ul>
        </div>
			</td>
		</tr>
		<tr class="filtersContainer">
			<td colspan="{{count($columns)+1}}">
				<form action="" method="post">
					<div class="btn-group">
						<input type="submit" value="Filter Results" class="btn btn-small btn-primary">
					</div>
					<div class="appendFilters">
						
					</div>
					<input type="hidden" value='0' name="filter_count" class="filterCount">
					<input type="hidden" value='f' name="action">
				</form>
			</td>
		</tr>
		<tr>
			@foreach ($columns as $column)
				@if (in_array($column['field'], $header['list']))
					<th>{{Util::cleanName($column['field'])}}</th>
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

					@elseif ($column['type'] == "tinyint")
									<input type="checkbox" {{($entry->{$column['field']} == 1)? "checked='checked'" : ""}}
										data-checked="{{$entry->{$column['field']} }}" data-id="{{$entry->id}}"
										data-url='{{str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'])}}' 
										name="{{$column['field']}}" class="infuseBoolean">
					@elseif (array_key_exists("display_order", $column)): ?>
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
						<li><a href="?action=e&id={{$entry->id}}">Edit</a></li>
						<li><a href="?action=d&id={{$entry->id}}" onclick="return confirm('Confirm delete?');">Delete</a></li>
						<li><a href="?action=cd&id={{$entry->id}}">Duplicate</a></li>
				  </ul>
				</div>
				
			</td>
		</tr>
		@endforeach

		@if ($header['pagination']['count'] < 1)
		<tr>
			<td colspan="{{count($columns)+1}}">
				<div class="hero-unit">
				  <h1>{{$header['name']}} listing is empty.</h1>
				  <p>To create the first one click the create button below.</p>
				  <p>
				    <a href="?action=c" class="btn btn-success btn">
				      Create {{$header['name']}}
				    </a>
				  </p>
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
	  		@if (isset($header['filters']))
	  			<?php  $filters = "action=f&filter_count=".count($header['filters']).""; ?>
	  		@endif
	  		<li><a href="?pg={{$pagination['active_page']-1}}">&laquo;</a></li>
	  	@else
	  		<li class="disabled"><a href="javascript: void(0)">&laquo;</a></li>
	  	@endif
	  	
	  	@if ($pagination['count'] > $pagination['limit'])
	  		<?php $times = ceil((int)$pagination['count']/(int)$pagination['limit']); ?>
	  		@for ($i=1; $i < $times+1; $i++)
	  			<li class="{{($pagination['active_page'] == $i)? "active" : ""}}">
	  				<a href="?pg={{$i}}">
	  					{{$i}}
	  				</a>
	  			</li>
	  	@endfor
	  	 <li><a href="?pg=a">View All</a></li>
	  	@endif
	  	
	  	@if (isset($times) && $pagination['active_page'] != $times)
	    	<li><a href="?pg={{$pagination['active_page']+1}}">&raquo;</a></li>
	    @else
	    	<li class="disabled"><a href="javascript: void(0)">&raquo;</a></li>
	   	@endif
	    
	  </ul>
	</div>
	@endif


</div>



