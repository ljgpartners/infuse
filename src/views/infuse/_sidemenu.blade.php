<div class="infuseSideMenu">
	<?php $count = 0; ?>
	<div class="accordion" id="accordion">
		@foreach ($navigation as $header => $n )

			<?php 
			$showSection = false;
			foreach ($n as $title => $link ) {
				$showSection = ((strpos($link,'::') !== false) || !$rolePermission || ($rolePermission && $user->can("{$link}_view")))? true : $showSection;
			}
			?>
		
			@if ($showSection)
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
						    	@if ((strpos($link,'::') !== false) || !$rolePermission || ($rolePermission && $user->can("{$link}_view")) )
						    		@if ((strpos($link,'::') !== false))
						    			<?php 
						    				$function = explode("::", $link);
						    				$class = $function[0];
						    				$function = $function[1]; 
						    			?>
						    			<li>
						    				<a href="{{URL::route('call_function')}}?cc={{$class}}&cf={{$function}}" 
						    				onclick='Infuse.confirmAndblockUI("{{$title}}", "{{$class.$function}}");'>
						    				{{$title}}
							    			</a>
							    			<div class="hide {{$class.$function}}">
													<h4>{{$title}}</h4>
													<div>
														<img width="32" height="32"  src="/packages/bpez/infuse/images/loading.gif" alt=""/>
													</div>
													</br>
												</div>
							    		</li>
						    		@else 
						    			<li><a href="/admin/resource/{{$link}}">{{$title}}</a></li>
						    		@endif
							    <li class="divider"></li>
							    @endif
								@endforeach
						</ul>
			      </div>
			    </div>
			  </div>
			  <?php $count++; ?>
		  @endif
	  @endforeach
	</div>

</div>
