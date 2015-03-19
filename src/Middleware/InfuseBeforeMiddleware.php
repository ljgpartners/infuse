<?php namespace Bpez\Infuse\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

class InfuseBeforeMiddleware implements Middleware {

  /**
  * Handle after request.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \Closure  $next
  * @return mixed
  */
  public function handle($request, Closure $next)
  {

    // Start Infuse before logic

    

    // End of Infuse before logic 

   return $next($request);
  }

}