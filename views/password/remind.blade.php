
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
	@elseif (isset($reason))
		<p class="errorMessage">{{$reason}}</p>
	@elseif (isset($success))
	  <p class="successMessage">An email with the reset link has been sent.</p>
	@else
	  <p class="normalMessage">Request password reset email link below.</p>
	@endif
	
  <form action="{{ action('\RemindersController@postRemind') }}" class="form-horizontal" method="POST" role="form">
  	<div class="form-group">
	  	<input type="text" name="email" value="Email" data-reset-name="Email" data-reset="1" class="infuseU placeholder validate form-control" data-validate='["presence","email"]'  autocomplete="off">
		</div>
		<div class="form-group">
			{{-- Laravel csrf token --}}
			<input type="hidden" name="_token" value="{!! csrf_token() !!}" />
  		<input type="submit" name="infuseLoginSubmit" value="go" class="infuseLoginSubmit"> 
  	</div>
  </form>
</div>
