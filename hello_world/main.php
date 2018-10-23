<?php

require_once __DIR__ . '/vendor/autoload.php';

use OpenCensus\Trace\Tracer;
//use OpenCensus\Trace\Exporter\EchoExporter;
use OpenCensus\Trace\Exporter\FileExporter;
use OpenCensus\Trace\Sampler\AlwaysSampleSampler;
use OpenCensus\Trace\Span;
use OpenCensus\Trace\Tracer\ContextTracer;

function function_to_trace()
{
    sleep(1);
}

function main()
{

    $sampler = new AlwaysSampleSampler();
    $file_exporter = 'logs/file' . (new DateTime())->format('dmYGisu') . '.json';
    $exporter = new FileExporter($file_exporter);
    $rt = Tracer::start($exporter, [
        'sampler' => $sampler,
        'skipReporting' => true
    ]);
    $tracer = $rt->tracer();

    Tracer::inSpan([
        'name' => 'root',
        'attributes' => [
            'example key' => 'example value'
        ]
    ], function() {
        return sleep(1);
    });

    Tracer::inSpan([
        'name' => 'child'
    ], function() {
        return sleep(1);
    });

    $tracer = $rt->tracer();

    $span = Tracer::startSpan();
    $scope = Tracer::withSpan($span);

    $cur_span = end($tracer->spans());

    $rt->onExit();
}

if (basename(__FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    main();
}