<div class="sideNavSlideOut">
	<?php $count = 0; ?>
	<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
		@foreach ($navigation as $header => $n )

		<?php 
			$showSection = false;
			foreach ($n as $title => $link ):
				$showSection = ((strpos($link,'::') !== false) || !$rolePermission || ($rolePermission && $user->can("{$link}_view")))? true : $showSection;
			endforeach;


		?>

			@if ($showSection)
		  <div class="panel panel-default">

		    <div class="panel-heading" role="tab" id="heading{{$count}}">
	        <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{$count}}" aria-expanded="true" aria-controls="collapse{{$count}}">
	        	<h4 class="panel-title">
	          {{$header}}
	          </h4>
	        </a>
		    </div>

		    <div id="collapse{{$count}}" class="panel-collapse collapse <?php echo ($count == 0)? "in" : ""; ?>" role="tabpanel" aria-labelledby="heading{{$count}}">
		    	<div class="panel-body">
		        <ul class="list-group">

		        	@if ($superAdmin || (isset($databaseConnectionType) && $databaseConnectionType == "pgsql" && Util::checkPsqlPagesExist($count+1)))
				    	<li class="list-group-item">
				    		<a href="/admin/page?infuse_pages_section={{$count+1}}">Pages</a>
				    	</li>
				    	@endif

							@foreach ($n as $title => $link )
					    	@if ((strpos($link,'::') !== false) || !$rolePermission || ($rolePermission && $user->can("{$link}_view")) )
					    		@if ((strpos($link,'::') !== false))
					    			<?php 
					    				$function = explode("::", $link);
					    				$class = $function[0];
					    				$function = $function[1]; 
					    			?>
					    			<li class="list-group-item">
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
					    			<li class="list-group-item"><a href="/admin/resource/{{$link}}">{{$title}}</a></li>
					    		@endif
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



