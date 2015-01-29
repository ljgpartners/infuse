<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>{{$title}}</title>
	<meta name="robots" content="noindex" />
	<meta name="viewport" content="width=device-width">
	 <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700|Nunito:400,300,700' rel='stylesheet' type='text/css'> 

	<link href='/packages/bpez/infuse/css/infuse.css' rel='stylesheet' type='text/css'>
	<script src="/packages/bpez/infuse/js/dependencies.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/ckeditor/ckeditor.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/ckeditor/adapters/jquery.js" type="text/javascript"></script>
	@if (Config::get('infuse::add_javascript') != "")
		<script src="{{Config::get('infuse::add_javascript')}}" type="text/javascript"></script>
	@endif
	<script src="/packages/bpez/infuse/js/infuse.min.js" type="text/javascript"></script>
	{{Util::infuse()}}
	 
</head>
<body class="{{Util::getControllerClassName()}} {{Util::getControllerClassNameWithMethod()}} 
	{{(isset($navigation))? "infuseWrapper" : ""}} {{($superAdmin)? "developer" : "" }}"  {{ (isset($user))? "data-user='{$user}'}" : ""}}>   

	

	@if (isset($navigation))
		@include('infuse::infuse._sidemenu')
	@endif

	<section>
		@if (isset($infusePagesSection) || (isset($firstNavLevel) && isset($secondNavLevel) && isset($navigation[$firstNavLevel][$secondNavLevel]))) 
			@include('infuse::infuse._nav_center')
		@endif
		<div class="container-fluid">
			<div class="row"> 
				<div class="contentPaddingWrapper">
					<div class="contentWrapper">
					{{$content}}
					</div>
				</div>
			</div>
		</div>
	</section>

</body>
</html>