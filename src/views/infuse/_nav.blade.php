<nav class="side">
	<a class="toggle" href="">
		<!--<div class="inner">
			<span class="glyphicon glyphicon-circle-arrow-right"></span>
			<span class="glyphicon glyphicon-circle-arrow-left hide"></span>
		</div>-->
	</a>

	<div class="sideNavElements">
		<a class="{{(isset($dashboardActive))? "active" : ""}}" href="/admin">
			<div class="inner">
				<span class="glyphicon glyphicon-home"></span>
			</div>
		</a>
		<!--
		<a href="">
			<div class="inner">
				<span class="glyphicon glyphicon-star"></span>
			</div>
		</a>
		-->
		<a href="" class="infuseManage {{(isset($manageActive))? "active" : ""}}" data-open="0">
			<div class="inner">
				<span class="glyphicon glyphicon-pencil"></span>
			</div>
		</a>
		@if (!$rolePermission || ($rolePermission && $user->can('infuse_user_view')) )
		<a class="{{(isset($userActive))? "active" : ""}}" href="/admin/user">
			<div class="inner">
				<span class="glyphicon glyphicon-user"></span>
			</div>
		</a>
		@endif
		@if (Config::get('infuse::admin_site_link') != "")
		<a target="_BLANK" class="siteLink" href="{{Config::get('infuse::admin_site_link')}}">
			<div class="inner">
				<span class="glyphicon glyphicon-eye-open"></span>
			</div>
		</a>
		@endif
		<a href="/admin/logout">
			<div class="inner">
				<span class="glyphicon glyphicon-off"></span>
			</div>
		</a>

		@if ($superAdmin && $rolePermission)
			<a href="/admin/permission">
				<div class="inner">
					<span class="glyphicon glyphicon-wrench"></span>
				</div>
			</a>
		@endif

		@if ($superAdmin && $rolePermission)
			<a href="/admin/role">
				<div class="inner">
					<span class="glyphicon glyphicon-link"></span>
				</div>
			</a>
		@endif

		@if ($superAdmin && $databaseConnectionType == "pgsql") 
			<a href="/admin/resource/infuse_page">
				<div class="inner">
					<span class="glyphicon glyphicon-file"></span>
				</div>
			</a>
		@endif
		
	</div>
</nav>
