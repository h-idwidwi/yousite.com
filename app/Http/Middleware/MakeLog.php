<?php
namespace App\Http\Middleware;

use App\Models\LogRequests;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class MakeLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $log = new LogRequests();

        $log->url = $request->fullUrl();
        $log->method = $request->method();
        $log->controller = $request->route()->getActionName();
        $log->controller_method = $request->route()->getActionMethod();
        $log->request_body = json_encode($request->all());
        $log->request_headers = json_encode($request->headers->all());
        $log->user_id = $request->user() ? $request->user()->id : null;
        $log->user_ip = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->response_status = $response->getStatusCode();
        $log->response_body = $response->getContent();
        $log->response_headers = json_encode($response->headers->all());
        $log->called_at = Carbon::now();

        $log->save();

        return $response;
    }
}
