@if (isset($_SESSION['infuse_stack']))

<?php 
$stack = $_SESSION['infuse_stack'];
$length = count($stack);
$count = 1;
?>

<ul class="breadcrumb">
	@foreach ($stack as $element)
		@if ($count == $length)
			<li class="active">{{Util::cleanName($element[0])}} </li>
		@else 
			<li>{{Util::cleanName($element[0])}} <span class="divider">/</span></li>
		@endif
		<?php $count++; ?>
	@endforeach
</ul>

@endif