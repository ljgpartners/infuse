<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>{{$title}}</title>
	<meta name="robots" content="noindex" />
	<meta name="viewport" content="width=device-width">


	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,300,700,800' rel='stylesheet' type='text/css'>
	<link href='/packages/bpez/infuse/css/infuse4.css' rel='stylesheet' type='text/css'>
	
	<script src="/packages/bpez/infuse/js/dependencies.min.js" type="text/javascript"></script>

	<script src="/packages/bpez/infuse/js/ckeditor/ckeditor.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/ckeditor/adapters/jquery.js" type="text/javascript"></script>

	@if (Config::get('infuse::add_javascript') != "")
		<script src="{{Config::get('infuse::add_javascript')}}" type="text/javascript"></script>
	@endif
	<script src="/packages/bpez/infuse/js/infuse.min.js" type="text/javascript"></script>


	<!--
	<script src="/packages/bpez/infuse/js/jquery-1.11.0.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/jquery-ui-timepicker-addon.js" type="text/javascript"></script>

	<script src="/packages/bpez/infuse/ckeditor/ckeditor.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/ckeditor/adapters/jquery.js" type="text/javascript"></script>

	<script src="/packages/bpez/infuse/js/magicsuggest-1.3.1.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/jquery.chained.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/jquery.blockui.js" type="text/javascript"></script>

	<script src="/packages/bpez/infuse/js/croppic.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/bpez.common.jquery.js" type="text/javascript"></script> -->

	<!--
	<script src="/packages/bpez/infuse/js/jquery.imgareaselect.pack.js" type="text/javascript"></script> -->
	
	<!--
	<style type="text/css">
	.infuseNav{ border-color: {{Config::get('infuse::colors.nav_border_color')}}; background-color: {{Config::get('infuse::colors.top_nav_background_color')}}; }
	.infuseNav .navElement a{ border-color: {{Config::get('infuse::colors.nav_highlight')}} !important; } 
	.infuseNav .siteLink{ background-color: {{Config::get('infuse::colors.button_color')}}; }
	.infuseNav{ background-image: url('{{Config::get('infuse::images.top_nav_background_snipe')}}'); {{Config::get('infuse::snipe_css')}} }
	.navElementCenter .logo{ margin-top: {{Config::get('infuse::logo_margin_top')}}px; }

	.infuseSideMenu{ background-color: {{Config::get('infuse::colors.side_menu_background')}}; }
	.infuseSideMenu .panel-body{ background-color: {{Config::get('infuse::colors.side_menu_open_background')}}; }
	.infuseSideMenu .panel-heading .accordion-toggle{ color: {{Config::get('infuse::colors.side_menu_section_title')}}; }
	.infuseSideMenu .nav-list a,  .infuseSideMenu .nav-list a:hover{ color: {{Config::get('infuse::colors.side_menu_sub_section_title')}}; }
	.infuseSideMenu .panel panel-default{ border-color: {{Config::get('infuse::colors.side_menu_border')}}; }

	.infuseTopButtonGroup .btn.mainColor, .infuseWrapper .jumbotron .btn.mainColor{ background-color: {{Config::get('infuse::colors.button_color')}} !important; }
	.infuseTopButtonGroup .btn.altColor{ background-color: {{Config::get('infuse::colors.button_alt_color')}} !important; }
	.infuseWrapper .submitButton{  background-color: {{Config::get('infuse::colors.button_color')}} !important; }
	.infuseScaffold .actionFixedNav input[type="submit"]{ background-color: {{Config::get('infuse::colors.button_color')}}; }

	.infuseLogin .infuseSiteTitle, .infuseLogin .infuseTextLogo, .infuseLogin .infuseTextLogoAbove{ color: {{Config::get('infuse::colors.login_text')}}; }
	.infuseLogin .infuseU, .infuseLogin .infuseP{ color: {{Config::get('infuse::colors.login_input_text')}}; }
	input.infuseLoginSubmit{ background-color: {{Config::get('infuse::colors.button_color')}}; }

	.fixTextEditorHighlightingClass{}


	style="background-image: url('{{Config::get('infuse::images.login_page_background')}}');"

	</style>
	-->

	{{Util::infuse()}}
	

	<!--<script data-main="/packages/bpez/infuse/js/main.js" src="/packages/bpez/infuse/js/require.js"></script>--> 
</head>
<body class="{{Util::getControllerClassName()}} {{Util::getControllerClassNameWithMethod()}} {{(isset($navigation))? "infuseWrapper" : ""}}"  {{ (isset($user))? "data-user='{$user}'}" : ""}}>

	<header>
		<div class="inner">
			<span class="adminTitle">La Jolla Group Corporate Site</span>
			<div class="headerLogos">
				@if (Config::get('infuse::images.logo') != "")
					<img class="logo" src="{{Config::get('infuse::images.logo')}}" > 
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