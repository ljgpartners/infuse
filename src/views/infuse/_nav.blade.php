<div class="infuseNav"> 
	<div class="innerNav">
		<div class="navElement"> 
			<a class="{{(isset($dashboardActive))? "active" : ""}}" href="/admin">
				<span>Dashboard</span>
			</a>
		</div>
		<div class="navElement">
			<a href="" class="infuseManage {{(isset($manageActive))? "active" : ""}}" data-open="0">
				<span>Manage</span>
			</a>
		</div>

		<div class="navElement navElementCenter">
			@if (Config::get('infuse::images.logo') != "")
				<img class="logo" src="{{Config::get('infuse::images.logo')}}" > 
			@else
				<div class="infuseTextLogo">Infuse</div>
			@endif
		</div>
		
		<div class="navElement">
			@if ($user->can('infuse_user_view'))
			<a class="{{(isset($userActive))? "active" : ""}}" href="/admin/user">
				<span>Users</span>
			</a>
			@endif
		</div>

		<div class="navElement">
			<a href="/admin/logout">
				<span>Logout</span>
			</a>
		</div>

		@if (Config::get('infuse::admin_site_link') != "")
		<a target="_BLANK" class="siteLink" href="{{Config::get('infuse::admin_site_link')}}">
			<span>site</span>
		</a>
		@endif

	</div>
</div>