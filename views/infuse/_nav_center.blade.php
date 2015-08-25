<?php
$navItems = array();

if (isset($infusePagesUnique) && $infusePagesUnique) {
	$count = 1;
	foreach ($navigation as $topKey => $set) {
		$firstNavLevel = $topKey;
		foreach ($set as $key => $n) {
			$secondNavLevel = $key;
			if (is_array($n)) {
				if ($n['unique'] == $infusePagesUnique) {
					$navItems = $n;
					break;
				}
				$count++;
			}
		}
		if (!empty($navItems)) {
			break;
		}
	}

} else if (isset($infusePagesSection)) {
	$count = 1;
	foreach ($navigation as $topKey => $set) {
		$firstNavLevel = $topKey;
		foreach ($set as $key => $n) {
			$secondNavLevel = $key;
			if (is_array($n)) {
				if ($infusePagesSection == $count) {
					$navItems = $n;
					break;
				}
				$count++;
			}
		}
		if (!empty($navItems)) {
			break;
		}
	}
} else {
	$count = 1;
	$infusePagesSection = null;
	foreach ($navigation as $topKey => $set) {
		foreach ($set as $key => $n) {
			if (is_array($n)) {
				if ($firstNavLevel == $topKey && $secondNavLevel == $key) {
					$infusePagesSection = $count;
					break;
				}
				$count++;
			}
		}
		if ($infusePagesSection != null) {
			break;
		}
	}
	$navItems = $navigation[$firstNavLevel][$secondNavLevel];
}

?>

<nav class="center" data-nav-level-active="{{$firstNavLevel}}{{$secondNavLevel}}">
	<div class="container-fluid">

	<div class="row">
		<div class="col-sm-12 col-xs-12">

			<h1>{{$navItems['name']}}</h1>
  		<p>{{$navItems['description']}}</p>

		</div>
	</div> <!-- end of row -->

	<div class="row">
		<div class="col-sm-12 col-xs-12">

		@if (($superAdmin && isset($databaseConnectionType) && $databaseConnectionType == "pgsql") || (isset($databaseConnectionType) && $databaseConnectionType == "pgsql" && Util::checkPsqlPagesExist($infusePagesSection)))
	  	<div class="linkWrapper {{(strpos(\Route::currentRouteAction(), "InfusePageController") !== FALSE )? "active" : ""}}">
	  		<a href="/admin/page?infuse_pages_section={{$infusePagesSection}}">Pages</a>
	  	</div>
  	@endif

		@foreach ($navItems as $title => $link )
			@if ($title != "name" && $title != "description" && $title != "unique" && $title != "permission")

				@if ((strpos($link,'::') !== false))
					<?php
						$function = explode("::", $link);
						$class = $function[0];
						$function = $function[1];
					?>
					<div class="linkWrapper {{(isset($resource) && $resource == $link)? "active" : ""}}">
						<a href="{{URL::route('call_function')}}?cc={{$class}}&cf={{$function}}"
						onclick='Infuse.confirmAndblockUI("{{$title}}", "{{$class.$function}}");'>
						{{$title}}
		  			</a>
		  			<div class="hide {{$class.$function}}">
							<h4>{{$title}}</h4>
							<div>
								<img width="32" height="32"  src="/bpez/infuse/images/loading.gif" alt=""/>
							</div>
							</br>
						</div>
		  		</div>
				@else
					<div class="linkWrapper {{(isset($resource) && $resource == $link)? "active" : ""}}">
					<a href="/admin/resource/{{$firstNavLevel}}/{{$secondNavLevel}}/{{$link}}">{{$title}}</a>
					</div>
				@endif
			@endif

		@endforeach
		</div>
	</div> <!-- end of row -->


	</div>
</nav>
