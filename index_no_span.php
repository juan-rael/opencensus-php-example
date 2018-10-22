<?php

require_once __DIR__ . '/vendor/autoload.php';

use OpenCensus\Trace\Tracer;
use OpenCensus\Trace\Exporter\FileExporter;

use OpenCensus\Trace\Sampler\AlwaysSampleSampler;


function calculatePi($accuracy = 1000000)
{
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

$exporter = new FileExporter('logs/file'.(new DateTime())->format('dmYGisu').'.json');
$rt = Tracer::start( $exporter , [
    'sampler' => new AlwaysSampleSampler(),
    'skipReporting' => true
]);

$tracer = $rt->tracer();

echo $tracer->spanContext()->enabled() . "\n";
$spans = $rt->tracer()->spans();

print_r( $spans );

calculatePi(1000);
fibonacci(50);