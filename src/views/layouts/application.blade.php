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
	
	<script src="/packages/bpez/infuse/js/jquery-1.11.0.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/jquery-ui-timepicker-addon.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/ckeditor/ckeditor.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/magicsuggest-1.3.1.js" type="text/javascript"></script>
	<!--
	<script src="/packages/bpez/infuse/js/jquery.imgareaselect.pack.js" type="text/javascript"></script> -->
	<script src="/packages/bpez/infuse/js/croppic.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/bpez.common.jquery.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/infuse.js" type="text/javascript"></script>

	<style type="text/css">
	.infuseNav{ border-color: {{Config::get('infuse::colors.nav_border_color')}}; background-color: {{Config::get('infuse::colors.top_nav_background_color')}}; }
	.infuseNav .navElement a{ border-color: {{Config::get('infuse::colors.nav_highlight')}} !important; } 
	.infuseNav .siteLink{ background-color: {{Config::get('infuse::colors.button_color')}}; }
	.infuseNav{ background-image: url('{{Config::get('infuse::images.top_nav_background_snipe')}}'); {{Config::get('infuse::snipe_css')}} }
	.navElementCenter .logo{ margin-top: {{Config::get('infuse::logo_margin_top')}}px; }

	.infuseSideMenu{ background-color: {{Config::get('infuse::colors.side_menu_background')}}; }
	.infuseSideMenu .accordion-inner{ background-color: {{Config::get('infuse::colors.side_menu_open_background')}}; }
	.infuseSideMenu .accordion-heading .accordion-toggle{ color: {{Config::get('infuse::colors.side_menu_section_title')}}; }
	.infuseSideMenu .nav-list a,  .infuseSideMenu .nav-list a:hover{ color: {{Config::get('infuse::colors.side_menu_sub_section_title')}}; }
	.infuseSideMenu .accordion-group{ border-color: {{Config::get('infuse::colors.side_menu_border')}}; }

	.infuseTopButtonGroup .btn.mainColor, .infuseWrapper .hero-unit .btn.mainColor{ background-color: {{Config::get('infuse::colors.button_color')}} !important; }
	.infuseTopButtonGroup .btn.altColor{ background-color: {{Config::get('infuse::colors.button_alt_color')}} !important; }
	.infuseWrapper .submitButton{  background-color: {{Config::get('infuse::colors.button_color')}} !important; }
	.infuseScaffold .actionFixedNav input[type="submit"]{ background-color: {{Config::get('infuse::colors.button_color')}}; }

	.infuseLogin .infuseSiteTitle, .infuseLogin .infuseTextLogo, .infuseLogin .infuseTextLogoAbove{ color: {{Config::get('infuse::colors.login_text')}}; }
	.infuseLogin .infuseU, .infuseLogin .infuseP{ color: {{Config::get('infuse::colors.login_input_text')}}; }
	input.infuseLoginSubmit{ background-color: {{Config::get('infuse::colors.button_color')}}; }

	.fixTextEditorHighlightingClass{}
	</style>
	

	<!--<script data-main="/packages/bpez/infuse/js/main.js" src="/packages/bpez/infuse/js/require.js"></script>-->
</head>
<body class="<?php echo (isset($navigation))? "infuseWrapper" : ""; ?>" style="background-image: url('{{Config::get('infuse::images.login_page_background')}}');">

	<?php if (isset($navigation)): ?>
		@include('infuse::infuse._nav')
		@include('infuse::infuse._sidemenu')
		<?php if ($superAdmin && $rolePermission): ?>
			@include('infuse::infuse._super_admin')
		<?php endif; ?>
	<?php endif; ?>

	<div class="container">
		{{$content}}
	</div>


	
</body>
</html>