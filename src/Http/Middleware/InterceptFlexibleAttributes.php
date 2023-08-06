<?php

namespace NovaFlexibleContent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NovaFlexibleContent\Http\FlexibleAttribute;
use NovaFlexibleContent\Http\ParsesFlexibleAttributes;
use NovaFlexibleContent\Http\TransformsFlexibleErrors;
use Symfony\Component\HttpFoundation\Response;

class InterceptFlexibleAttributes
{
    use ParsesFlexibleAttributes;
    use TransformsFlexibleErrors;

    /**
     * Handle the given request and get the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next) : Response
    {

        if (!$this->requestHasParsableFlexibleInputs($request)) {
            return $next($request);
        }

        $request->merge($this->getParsedFlexibleInputs($request));
        $request->request->remove(FlexibleAttribute::REGISTER_FLEXIBLE_FIELD_NAME);

        $response = $next($request);

        if (!$this->shouldTransformFlexibleErrors($response)) {
            return $response;
        }

        return $this->transformFlexibleErrors($response);
    }
}
