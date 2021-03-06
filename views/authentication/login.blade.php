<div class="jumbotron infuseLogin">

	@if (Config::get('infuse::config.company_logo') != "")
		<img class="logo" src="{{Config::get('infuse::config.company_logo')}}">
	@else
    <img class="logo" src="/bpez/infuse/images/infuseLogo.png" alt="">
	@endif

	@if (Config::get('infuse::config.site_title') != "")
		<div class="infuseSiteTitle">{{Config::get('infuse::config.site_title')}}</div>
	@endif

  @if (isset($error))
  	<p class="errorMessage">{{$error}}</p>
  @endif
  <form method="POST" action="" class="form-horizontal" role="form">
  	<div class="form-group">
  	<input type="text" name="infuseU" class="infuseU validate form-control" placeholder="Username" value="" data-reset-name="" data-reset="1" data-validate='["presence"]' autocomplete="off">
  	</div>
  	<div class="form-group">
  	<input type="text" name="infuseP" class="infuseP validate focusPassword form-control" placeholder="Password" value="" data-reset-name="" data-validate='["presence"]' data-reset="1"  autocomplete="off">
  	</div>
  	<div class="form-group">
    {{-- Laravel csrf token --}}
    <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
  	<input type="submit" name="infuseLoginSubmit" value="go" class="infuseLoginSubmit">
  	</div>
  </form>
</div>
