<?php

require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Trace\TraceClient;
use OpenCensus\Trace\Tracer;
use OpenCensus\Trace\Exporter\FileExporter;
use OpenCensus\Trace\Exporter\LoggerExporter;
use OpenCensus\Trace\Span;
use OpenCensus\Trace\TimeEvent;
use OpenCensus\Trace\Status;


function calculatePi($accuracy = 1000000){
    $pi = 4;
    $top = 4;
    $bot = 3;
    $minus = TRUE;

    for($i = 0; $i < $accuracy; $i++)
    {
        $pi += ( $minus ? -($top/$bot) : ($top/$bot) );
        $minus = ( $minus ? FALSE : TRUE);
        $bot += 2;
    }
    return $pi;
}

function fibonacci($n,$first = 0,$second = 1)
{
    $fib = [$first,$second];
    for($i=1;$i<$n;$i++)
    {
        $fib[] = $fib[$i]+$fib[$i-1];
    }
    return $fib;
}

$exporter = new FileExporter( 'logs/file' . ( new DateTime() )->format('dmYGisu') . '.json' );

$rt = Tracer::start( $exporter );

$tracer = $rt->tracer( );

$span = new Span( [
    'name' => 'SpanNuevo',
    'status' => new Status(0 , 'Not Logging')
] );

$span->setStartTime( time() );

$attr = new stdClass( );
$attr->calculatePi = 1000;
$attr->hello = 'world';
$attr->bye = 'bye';
sleep( 2 );
$span->addAttributes( json_decode( json_encode( $attr ) , true ) );
$span->setStatus( 200, 'OK' );
Tracer::withSpan( $span );
$calcpi = Tracer::inSpan( [ 'name' => 'calculatePi' ] , 'calculatePi', [ 1000 ] );
$span = Tracer::startSpan( [ 'name' => 'fibonacci-operation' ] );

// Opens a scope that attaches the span to the current context
$scope = Tracer::withSpan( $span );
try {
    $fibonacci = fibonacci( 50 );
} finally {
    // Closes the scope (ends the span)
    $scope->close( );
}

$rt->onExit();