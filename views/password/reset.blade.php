
<div class="jumbotron infuseLogin">

  @if (Config::get('infuse::config.company_logo') != "")
    <img class="logo" src="{{Config::get('infuse::config.company_logo')}}">
  @else
    <img class="logo" src="/bpez/infuse/images/infuseLogo.png" alt="">
  @endif

  @if (Config::get('infuse::config.site_title') != "")
    <div class="infuseSiteTitle">{{Config::get('infuse::config.site_title')}}</div>
  @endif

   @if ($create)
    <p class="normalMessage">Create password</p>
  @else
    <p class="normalMessage">Reset password</p>
  @endif


  @if (isset($error))
	  <p class="errorMessage">{{$error}}</p>
	@endif

  <form action="{{ action('\RemindersController@postReset') }}" class="form-horizontal" method="POST" role="form">
  	<div class="form-group">
      <input type="hidden" name="token" value="{{$token}}">
      <input type="text" name="email" placeholder="Email"  value="" data-reset-name="" data-reset="1"  class="infuseU validate form-control" data-validate='["presence","email"]' autocomplete="off">
    </div>
    <div class="form-group">
    	<input type="text" name="password" placeholder="Password" value="" data-reset-name="" data-reset="1"  class="infuseP validate focusPassword form-control" data-validate='["presence"]' autocomplete="off">
    </div>
    <div class="form-group">
      <input type="text" name="password_confirmation" placeholder="Password repeat" value="" data-reset-name="" data-reset="1" class="infuseP validate focusPassword form-control" data-validate='["presence"]' autocomplete="off">
    </div>
    <div class="form-group">
      {{-- Laravel csrf token --}}
      <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
      <input type="submit" name="infuseLoginSubmit" value="go" class="infuseLoginSubmit">
    </div>
  </form>
</div>
