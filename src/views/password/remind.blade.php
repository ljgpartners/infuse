<div class="jumbotron infuseLogin">
  
	@if (Config::get('infuse::images.logo') != "")
		<img class="logo" src="{{Config::get('infuse::images.logo')}}">
	@else
		<div class="infuseTextLogo">Infuse</div>
	@endif

	@if (isset($error))
	  <p class="errorMessage">{{$error}}</p>
	@elseif (isset($reason))
		<p class="errorMessage">{{$reason}}</p>
	@elseif (isset($success))
	  <p class="successMessage">An email with the reset link has been sent.</p>
	@else
	  <p>Request password reset email link below.</p>
	@endif
  <form action="{{ action('RemindersController@postRemind') }}" method="POST">
  	<input type="text" name="email" value="Email" data-reset-name="Email" data-reset="1" class="infuseU placeholder validate" data-validate='["presence","email"]'  autocomplete="off">
  	<input type="submit" name="infuseLoginSubmit" value="go" class="infuseLoginSubmit"> 
  </form>
</div>