<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Helpers\Helper;
use App\Models\SessionParam;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Http\Response;
use Carbon\Carbon;
// use Illuminate\Http\RedirectResponse;

use App\Models\VisitorSession;

class IndexController extends Controller
{
	public function __construct()
	{

	}

	/*
		WAITING ROOM LOGIC
	*/
    public function index(Request $request)
    {
    	if(View::exists('index')) {
    		$waitingTime = env('WAITING_TIME', VisitorSession::DEFAULT_WAITING_TIME);
    		$sessionLifetime = env('SESSION_LIFETIME', VisitorSession::DEFAULT_SESSION_LIFETIME);
    		$helper = new Helper;

    		$var;
			if(!$request->session()->has(SessionParam::SESSION_KEY)) {
	    		//create visitor session
	    		$visitorSession = new VisitorSession;
	    		$visitorSession->session_key = $visitorSession->generateSessionKey();
	    		$visitorSession->status = VisitorSession::SESSION_STATUS_NEW;
	    		$visitorSession->active = VisitorSession::SESSION_ACTIVE;
	    		$visitorSession->save();

	    		$var = [
	    			SessionParam::SESSION_KEY => $visitorSession->session_key,
	    			SessionParam::SESSION_STATUS => $visitorSession->status,
	    			SessionParam::SESSION_ACTIVE => $visitorSession->active
	    		];

	    		//save to session
	    		$request->session()->put(SessionParam::SESSION_KEY, $visitorSession->session_key);
	    		$request->session()->put(SessionParam::SESSION_STATUS, $visitorSession->status);
	    		$request->session()->put(SessionParam::SESSION_ACTIVE, $visitorSession->active);
	    	} 
	    	else 
	    	{
	    		//check whether the session is expired
	    		$visitorSession = VisitorSession::where('session_key', $request->session()->get(SessionParam::SESSION_KEY))
	    							->first();

	    		$now = Carbon::now();
	    		$diff = $now->diffInSeconds($visitorSession->created_at);
	    		if($diff > $sessionLifetime)
	    		{
	    			$visitorSession->active = VisitorSession::SESSION_NOT_ACTIVE;
	    			$visitorSession->save();

	    			//re-create new session
	    			$newVisitorSession = new VisitorSession;
		    		$newVisitorSession->session_key = $newVisitorSession->generateSessionKey();
		    		$newVisitorSession->status = VisitorSession::SESSION_STATUS_NEW;
		    		$newVisitorSession->active = VisitorSession::SESSION_ACTIVE;
		    		$newVisitorSession->save();

		    		$var = [
		    			SessionParam::SESSION_KEY => $newVisitorSession->session_key,
		    			SessionParam::SESSION_STATUS => $newVisitorSession->status,
		    			SessionParam::SESSION_ACTIVE => $newVisitorSession->active
		    		];

		    		//save to session
		    		$request->session()->put(SessionParam::SESSION_KEY, $newVisitorSession->session_key);
		    		$request->session()->put(SessionParam::SESSION_STATUS, $newVisitorSession->status);
		    		$request->session()->put(SessionParam::SESSION_ACTIVE, $newVisitorSession->active);
	    		}
	    		else
	    		{
		    		$var = [
		    			SessionParam::SESSION_KEY => $request->session()->get(SessionParam::SESSION_KEY),
		    			SessionParam::SESSION_STATUS => $request->session()->get(SessionParam::SESSION_STATUS),
		    			SessionParam::SESSION_ACTIVE => $request->session()->get(SessionParam::SESSION_ACTIVE)
		    		];
		    	}
	    	}

	    	$response = new Response(view('index', $var));
    		$response->withCookie(cookie(SessionParam::SESSION_KEY, $request->session()->get(SessionParam::SESSION_KEY), $sessionLifetime));
    		return $response;
    	}
    	
    	return view('welcome');
    }

    /*
		REDIRECT TO MAGENTO
    */
    public function redirect(Request $request)
    {
    	$redirectUrl = env('REDIRECTURL', env('APP_URL'));
    	$sessionLifetime = env('SESSION_LIFETIME', VisitorSession::DEFAULT_SESSION_LIFETIME);

    	$cookie = cookie(SessionParam::SESSION_KEY, $request->cookie(SessionParam::SESSION_KEY), $sessionLifetime, "/", env('APP_URL'));
    	return redirect($redirectUrl)->withCookie($cookie);
    }
}
