<nav class="side">

	@if (isset($navigation) && Config::get('infuse::config.site_title') != "")
	<div class="topNav">
		<span class="adminTitle">{{Config::get('infuse::config.site_title')}}</span>
	</div>
	@endif

	<a class="navLink {{(isset($dashboardActive))? "active" : ""}}" href="/admin">
		<div class="inner">
			<span class="glyphicon glyphicon-dashboard"></span> Dashboard
		</div>
	</a>

	<a href="" class="navLink infuseManage {{(isset($manageActive))? "active" : ""}}" data-open="0">
		<div class="inner">
			<span class="glyphicon glyphicon-pencil"></span> Manage Content
		</div>
	</a>

	
	

	<div class="sideNavSlideOut">
		<?php $count = 0; ?>
		<?php $countInner = 0; ?>
		<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
			@foreach ($navigation as $firstNavLevel => $topLevel )


				
			  <div class="panel panel-default">

			    <div class="panel-heading" role="tab" id="heading{{$count}}">
		        <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{$count}}" aria-expanded="true" aria-controls="collapse{{$count}}">
		        	<h4 class="panel-title">
		          {{$topLevel['name']}}
		          </h4>
		        </a>
			    </div>

			    <div id="collapse{{$count}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{{$count}}">
			    	<div class="panel-body">
			        <ul class="list-group">

								@foreach ($topLevel as $secondNavLevel => $secondLevel )
									
									@if (is_array($secondLevel)) 
										@if ($superAdmin || (isset($databaseConnectionType) && $databaseConnectionType == "pgsql" && Util::checkPsqlPagesExist($countInner+1))) 
							    	<li class="list-group-item" data-active="{{$firstNavLevel}}{{$secondNavLevel}}">
							    		<a href="/admin/page?infuse_pages_section={{$countInner+1}}">{{$secondLevel['name']}}</a>
							    	</li>
							    	@elseif (count($secondLevel) > 1)
							    	<?php 
								    	$keys = array_keys($secondLevel);
											$key = $keys[2];
											$value = $secondLevel[$key]
										?>
							    	<li class="list-group-item"  data-active="{{$firstNavLevel}}{{$secondNavLevel}}">
							    		<a href="/admin/resource/{{$firstNavLevel}}/{{$secondNavLevel}}/{{$value}}">{{$secondLevel['name']}}</a>
							    	</li>
							    	@endif
							    	<?php $countInner++; ?>
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


	@if (!$rolePermission || ($rolePermission && $user->can('infuse_user_view')) )
	<a class="navLink {{(isset($userActive))? "active" : ""}}" href="/admin/user">
		<div class="inner">
			<span class="glyphicon glyphicon-user"></span> Users
		</div>
	</a>
	@endif


	@if (Config::get('infuse::config.admin_site_link') != "")
	<a target="_BLANK" class="navLink siteLink" href="{{Config::get('infuse::config.admin_site_link')}}">
		<div class="inner">
			<span class="glyphicon glyphicon-eye-open"></span> Website
		</div>
	</a>
	@endif

	<a class="navLink" href="/admin/logout">
		<div class="inner">
			<span class="glyphicon glyphicon-off"></span> Sign out
		</div>
	</a>

	@if ($superAdmin && $rolePermission)
		<a class="navLink" href="/admin/permission">
			<div class="inner">
				<span class="glyphicon glyphicon-wrench"></span> Permissions
			</div>
		</a>
	@endif

	@if ($superAdmin && $rolePermission)
		<a class="navLink" href="/admin/role">
			<div class="inner">
				<span class="glyphicon glyphicon-link"></span> Roles
			</div>
		</a>
	@endif

	@if ($superAdmin && $databaseConnectionType == "pgsql")  
		<a class="navLink" href="/admin/resource/infuse/page/infuse_page">
			<div class="inner">
				<span class="glyphicon glyphicon-file"></span> Infuse pages
			</div>
		</a>
	@endif

	<div class="logoSideNav">
		<img src="/bpez/infuse/images/infuseLogo.png" alt="">
	</div>
	

</nav>

