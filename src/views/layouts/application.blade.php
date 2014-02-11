<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>{{$title}}</title>
	<meta name="robots" content="noindex" />
	<meta name="viewport" content="width=device-width">

	<link href='http://fonts.googleapis.com/css?family=Raleway|Open+Sans:400italic,400,700,600' rel='stylesheet' type='text/css'>
	<link href='/packages/bpez/infuse/css/bootstrap-combined.min.css' rel='stylesheet' type='text/css'>
	<link href='/packages/bpez/infuse/css/jquery-ui.min.css' rel='stylesheet' type='text/css'>
	<link href='/packages/bpez/infuse/css/jquery-ui-timepicker-addon.css' rel='stylesheet' type='text/css'>
	<link href='/packages/bpez/infuse/css/magicsuggest-1.3.1.css' rel='stylesheet' type='text/css'>
	<link href='/packages/bpez/infuse/css/infuse.css' rel='stylesheet' type='text/css'>

	<link href='/packages/bpez/infuse/css/imgareaselect/imgareaselect-animated.css' rel='stylesheet' type='text/css'>
	<link href='/packages/bpez/infuse/css/imgareaselect/imgareaselect-default.css' rel='stylesheet' type='text/css'>
	<link href='/packages/bpez/infuse/css/imgareaselect/imgareaselect-deprecated.css' rel='stylesheet' type='text/css'>
	
	<script src="/packages/bpez/infuse/js/jquery-1.9.1.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/jquery-ui-timepicker-addon.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/ckeditor/ckeditor.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/magicsuggest-1.3.1.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/jquery.imgareaselect.pack.js" type="text/javascript"></script>
	<script src="/packages/bpez/infuse/js/infuse.js" type="text/javascript"></script>
	

	<!--<script data-main="/packages/bpez/infuse/js/main.js" src="/packages/bpez/infuse/js/require.js"></script>-->
</head>
<body class="<?php echo (isset($navigation))? "infuseWrapper" : ""; ?>">

	<?php if (isset($navigation)): ?>
		@include('infuse::infuse._nav')
		@include('infuse::infuse._sidemenu')
	<?php endif; ?>

	<div class="container">
		{{$content}}
	</div>
	
</body>
</html>