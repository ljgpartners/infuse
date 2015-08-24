@if($header['importFromModel'])
<div class="infuseTopButtonGroup">

	<?php $import = $header['importFromModel']; ?>

  <div class="btn-group">
    <button class="btn btn-default altColor btn-info  dropdown-toggle" data-toggle="dropdown">Import from <span class="caret"></span></button>
    <ul class="dropdown-menu filtersDropDown">
		<li><a href="" data-toggle="modal" data-target="#importModal{{$import[0]}}" class="">{{((isset($import[2]) && $import[2]['name'])? $import[2]['name'] : Util::cleanName($import[0]) )}}</a></li>
    </ul>
  </div>

  <?php
  	if (isset($import[2]['related_model'])) {
  		$foriegnKey = key($import[2]['related_model']);
  		$parentId = current($import[2]['related_model']);
  		$parentId = $parentId();
  	} else {
  		$foriegnKey = "";
  		$parentId = "";
  	}
  ?>
  <div
	id="importModal{{$import[0]}}"
	class="importModal modal fade"
	tabindex="-1"
	role="dialog"
	aria-labelledby="myModalLabel"
	aria-hidden="true"
	data-map='{{json_encode($import[2]['map'])}}'
	data-list='{{json_encode($import[2]['list'])}}'
	data-child="{{$import[0]}}"
	data-resource="{{$import[1]}}"
	data-first="1"
	data-model-importing-to="{{get_class($entries)}}"
	data-related-foriegn-key='{{$foriegnKey}}'
	data-related-parent-id='{{$parentId}}'
	data-model-importing-to-id="{{Util::get("id")}}" >

	  <div class="modal-header">
			<h1>{{((isset($import[2]['name']))? $import[2]['name'] : Util::cleanName($import[0]) )}} <small>{{((isset($import[2]['description']))? $import[2]['description'] : "" )}}</small></h1>

			@if (empty($foriegnKey) && empty($parentId))
			<ul class="nav nav-tabs">
			  <li class="active">
			  	<a href="#search{{$import[0]}}" data-toggle="tab">SEARCH</a>
			  </li>
			  @if (isset($import[2]['advanced_search']))
			  <li>
			  	<a href="#browse{{$import[0]}}" data-toggle="tab">ADVANCED SEARCH</a>
			  </li>
			  @endif
			</ul>



			<div class="tab-content">
				  <div class="tab-pane active" id="search{{$import[0]}}">
				  	<div class="searchBox">
				  		<form class="search">
				  			<input class="searchInput placeholder" name="s" type="text" value="Search..." data-reset-name="Search..." data-reset="1">
					  		<button type="submit" class="btn btn-default mainColor" href="">SEARCH</button>
				  		</form>
				  	</div>
				  </div>

				  @if (isset($import[2]['advanced_search']))
				  <div class="tab-pane" id="browse{{$import[0]}}">
				  	<p>Browse entries ordered by closest to the Latitude & Longitude point with search radius in miles. Default search radius is 25 miles. Add a search term to narrow results down.</p>
				  	<div class="searchBox">
				  		<form class="advancedSearch">
				  			<input class="searchField searchInput placeholder" name="s" type="text" value="Search..." data-reset-name="Search..." data-reset="1">
				  			<input class="searchLatitudeLongitude searchInput placeholder" name="latitude_longitude" type="text" value="Latitude, Longitude" data-reset-name="Latitude, Longitude" data-reset="1">
				  			<input class="distance searchInput placeholder" name="distance" type="text" value="25" data-reset-name="25" data-reset="1">
					  		<button type="submit" class="btn btn-default mainColor" href="">SEARCH</button>
				  		</form>
				  	</div>
				  </div>
				  @endif
			</div>
			@endif
	  </div>
	  <div class="modal-body">
	    <table class="table  table-bordered table-striped">
	    	<tr>
	    		@foreach ($import[2]['list'] as $column)
	    			<th>{{Util::cleanName($column)}}</th>
	    		@endforeach
	    			<th></th>
	    	</tr>
	    </table>

	    <div class="loading">
	    	<img src="/packages/bpez/infuse/images/loading.gif" alt="">
	    </div>
	  </div>
	  <div class="modal-footer">
	  	<!--<a href="" class="loadMoreResults">LOAD MORE RESULTS</a>-->
		<div class="text-center">
			<ul class="pagination pagination-sm">
		  		<li class="disabled"><a href="" class="loadMoreResultsPrev" data-allow="0">&laquo;</a></li>
		  		<li class="disabled"><a href="" class="loadMoreResultsNext" data-allow="0">&raquo;</a></li>
	  		</ul>
		</div>

	  	<button class="btn btn-default altColor btn-info" data-dismiss="modal" aria-hidden="true">Close</button>
	  </div>
	</div>



</div>
@endif


<style media="screen">

	.importModal  {
		width:90%;
		height: 90%;
		left: 5%;
		top: 5%;
		background-color: #F2F2F2;
	}



</style>
