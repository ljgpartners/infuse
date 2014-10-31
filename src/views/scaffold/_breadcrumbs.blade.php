@if (isset($_SESSION['infuse_stack']))

<?php 
$stack = $_SESSION['infuse_stack'];
$length = count($stack);
$count = 1;
?>

<ol class="breadcrumb pull-right">
	@foreach ($stack as $element)
		@if ($count == $length)
			<li class="active">{{Util::cleanName($element[0])}} </li>
		@else 
			<li>{{Util::cleanName($element[0])}} </li>
		@endif
		<?php $count++; ?>
	@endforeach
</ol>

@endif
