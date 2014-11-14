<div class="jumbotron infuseLogin">

  @if (Config::get('infuse::company_logo') != "")
    <img class="logo" src="{{Config::get('infuse::company_logo')}}">
  @else
    <div class="infuseTextLogo">Infuse</div>
  @endif

  @if (Config::get('infuse::site_title') != "")
    <div class="infuseSiteTitle">{{Config::get('infuse::site_title')}}</div>
  @endif
  
   @if ($create)
    <p class="normalMessage">Create password</p>
  @else
    <p class="normalMessage">Reset password</p>
  @endif
  

  @if (isset($error))
	  <p class="errorMessage">{{$error}}</p>
	@endif

  <form action="{{ action('RemindersController@postReset') }}" class="form-horizontal" method="POST" role="form">
  	<div class="form-group">
      <input type="hidden" name="token" value="{{$token}}">
      <input type="text" name="email" value="Email" data-reset-name="Email" data-reset="1"  class="infuseU placeholder validate form-control" data-validate='["presence","email"]' autocomplete="off">
    </div>
    <div class="form-group">
    	<input type="text" name="password" value="Password" data-reset-name="Password" data-reset="1"  class="infuseP placeholder validate focusPassword form-control" data-validate='["presence"]' autocomplete="off">
    </div>
    <div class="form-group">
      <input type="text" name="password_confirmation" value="Password repeat" data-reset-name="Password repeat" data-reset="1" class="infuseP placeholder validate focusPassword form-control" data-validate='["presence"]' autocomplete="off">
    </div>
    <div class="form-group">
  	 <input type="submit" name="infuseLoginSubmit" value="go" class="infuseLoginSubmit">
    </div>
  </form>
</div>

