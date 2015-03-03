<?php namespace Bpez\Infuse\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

class InfuseAfterMiddleware implements Middleware {

  /**
  * Handle after request.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \Closure  $next
  * @return mixed
  */
  public function handle($request, Closure $next)
  {
    $response = $next($request);

    // Start Infuse after logic

    if (\Session::has('infuse_page_values')) {
      \Session::forget('infuse_page_values');
      \Session::forget('infuse_page_extract_current');
    }

    // End of Infuse after logic 

    return $response;
  }

}