<div class="hero-unit infuseLogin">

  @if (Config::get('infuse::images.logo') != "")
    <img class="logo" src="{{Config::get('infuse::images.logo')}}">
  @else
    <div class="infuseTextLogo">Infuse</div>
  @endif

  <p>Reset</p>

  @if (isset($error))
	  <p class="errorMessage">{{$error}}</p>
	@endif
  <form action="{{ action('RemindersController@postReset') }}" method="POST">
  	<input type="hidden" name="token" value="{{$token}}">
  	<input type="text" name="email" value="Email" data-reset-name="Email" data-reset="1"  class="infuseU placeholder validate" data-validate='["presence","email"]' autocomplete="off">
  	<input type="text" name="password" value="Password" data-reset-name="Password" data-reset="1"  class="infuseP placeholder validate focusPassword" data-validate='["presence"]' autocomplete="off">
    <input type="text" name="password_confirmation" value="Password repeat" data-reset-name="Password repeat" data-reset="1" class="infuseP placeholder validate focusPassword" data-validate='["presence"]' autocomplete="off">
  	<input type="submit" name="infuseLoginSubmit" value="go" class="infuseLoginSubmit">
  </form>
</div>

