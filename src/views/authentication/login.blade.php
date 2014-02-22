<div class="hero-unit infuseLogin">

	@if (Config::get('infuse::titles.login_above_logo') != "")
		<div class="infuseTextLogoAbove">{{Config::get('infuse::titles.login_above_logo')}}</div>
	@endif

	@if (Config::get('infuse::images.logo') != "")
		<img class="logo" src="{{Config::get('infuse::images.logo')}}">
	@else
		<div class="infuseTextLogo">Infuse</div>
	@endif

	@if (Config::get('infuse::titles.login_site_title') != "")
		<div class="infuseSiteTitle">{{Config::get('infuse::titles.login_site_title')}}</div>
	@endif

  @if (isset($error))
  	<p class="errorMessage">{{$error}}</p>
  @endif
  <form method="POST" action="">
  	<input type="text" name="infuseU" class="infuseU placeholder validate" value="Username" data-reset-name="Username" data-reset="1" data-validate='["presence"]' autocomplete="off">
  	<input type="text" name="infuseP" class="infuseP placeholder validate focusPassword" value="Password" data-reset-name="Password" data-validate='["presence"]' data-reset="1"  autocomplete="off">
  	<input type="submit" name="infuseLoginSubmit" value="go" class="infuseLoginSubmit" style="background-color: {{Config::get('infuse::colors.button_color')}};">
  </form>
</div>