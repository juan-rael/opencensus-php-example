<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenCensus\Trace\Tracer;
use OpenCensus\Trace\Span;
use App\User;


use Google\Cloud\Bigtable\V2\BigtableClient;


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
    function new_one(){
        $instance_id = 'quickstart-instance-php';
        $table_id = 'bigtable-php-table';
        
        $bigtableClient = new BigtableClient();
        $formattedTableName = $bigtableClient->tableName(getenv('PROJECT_ID'), $instance_id, $table_id);
        $stream = $bigtableClient->readRows($formattedTableName);
    }
}
