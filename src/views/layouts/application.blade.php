<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>{{$title}}</title>
	<meta name="robots" content="noindex" />
	<meta name="viewport" content="width=device-width">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,300,700,800' rel='stylesheet' type='text/css'>
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

	<header>
		<div class="inner">
			@if (isset($navigation) && Config::get('infuse::site_title') != "")
				<span class="adminTitle">{{Config::get('infuse::site_title')}}</span>
			@endif
			<div class="headerLogos">
				@if (isset($navigation) && Config::get('infuse::company_logo') != "")
					<img class="logo" src="{{Config::get('infuse::company_logo')}}" > 
				@endif
				<a href="">
					<img alt="Brand" src="/packages/bpez/infuse/images/infuse4/infuse4Brand.png">
				</a>
			</div>
		</div>
	</header> 

	@if (isset($navigation))
		@include('infuse::infuse._nav')
		@include('infuse::infuse._sidemenu')
	@endif

	<section>
		{{$content}}
	</section>

</body>
</html>