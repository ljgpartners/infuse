<div class="jumbotron infuseLogin">

	@if (Config::get('infuse::company_logo') != "")
		<img class="logo" src="{{Config::get('infuse::company_logo')}}">
	@else
		<div class="infuseTextLogo">Infuse</div>
	@endif

	@if (Config::get('infuse::site_title') != "")
		<div class="infuseSiteTitle">{{Config::get('infuse::site_title')}}</div>
	@endif

  @if (isset($error))
  	<p class="errorMessage">{{$error}}</p>
  @endif
  <form method="POST" action="" class="form-horizontal" role="form">
  	<div class="form-group">
  	<input type="text" name="infuseU" class="infuseU placeholder validate form-control" value="Username" data-reset-name="Username" data-reset="1" data-validate='["presence"]' autocomplete="off">
  	</div>
  	<div class="form-group">
  	<input type="text" name="infuseP" class="infuseP placeholder validate focusPassword form-control" value="Password" data-reset-name="Password" data-validate='["presence"]' data-reset="1"  autocomplete="off">
  	</div>
  	<div class="form-group">
  	<input type="submit" name="infuseLoginSubmit" value="go" class="infuseLoginSubmit">
  	</div>
  </form>
</div>