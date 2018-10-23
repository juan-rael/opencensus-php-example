<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenCensus\Trace\Tracer;
use OpenCensus\Trace\Span;
use App\User;

class IndexController extends Controller
{
    function index(){
    	return Tracer::inSpan([
    	    'name' => 'controller.indexController.index.view'
        ], function(){

            $users = Tracer::inSpan([
                'name' => 'controller.indexController.index.users'
            ], function(){
                return User::get();
            });
    	    return view('bienvenido',[
                'users' => $users
            ]);
        });
    }
}
