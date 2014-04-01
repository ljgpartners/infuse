<div class="infuseSideMenu">
	<?php $count = 0; ?>
	<div class="accordion" id="accordion">
		@foreach ($navigation as $header => $n )

	  <div class="accordion-group">
	    <div class="accordion-heading">
	      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse{{$count}}">
	        {{$header}}
	      </a>
	    </div>
	    <div id="collapse{{$count}}" class="accordion-body collapse <?php echo ($count == 0)? "in" : ""; ?>">
	      <div class="accordion-inner">
	        <ul class="nav nav-list ">
				    @foreach ($n as $title => $link )
				    	@if (!$rolePermission || ($rolePermission && $user->can("{$link}_view")) )
					    <li><a href="/admin/resource/{{$link}}">{{$title}}</a></li>
					    <li class="divider"></li>
					    @endif
						@endforeach
				</ul>
	      </div>
	    </div>
	  </div>
	  <?php $count++; ?>
	  @endforeach
	</div>

</div>

