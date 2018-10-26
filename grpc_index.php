<?php

require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Bigtable\V2\BigtableClient;
use OpenCensus\Trace\Integrations\Grpc;
use Google\Cloud\Trace\TraceClient;
use OpenCensus\Trace\Tracer;
use Google\ApiCore\ApiException;
use OpenCensus\Version;
use OpenCensus\Trace\Exporter\StackdriverExporter;


echo Version::VERSION . "\n";

$traceClient = new TraceClient([
    'projectId' => getenv('PROJECT_ID'),
]);
$tracerClient = [
    'client' => $traceClient
];

$exporter = new StackdriverExporter($tracerClient);
$trace = $traceClient->trace('GrpcSample');

Tracer::start($exporter);
Grpc::load();

$bigtableClient = new BigtableClient();

$instance_id = 'quickstart-instance-php';
$table_id = 'bigtable-php-table';
try {
    $formattedTableName = $bigtableClient->tableName(getenv('PROJECT_ID'), $instance_id, $table_id);
    // Read all responses until the stream is complete
    $stream = $bigtableClient->readRows($formattedTableName);

    foreach ($stream->readAll() as $element) {
        $iterator = iterator_to_array($element->getChunks()->getIterator());
        foreach ($iterator as $cellChunk) {
            echo $cellChunk->getRowKey();
            echo ' [ ' . $cellChunk->getValue() . ' ] ';
            echo '( ' . $cellChunk->getTimestampMicros() . ')';
            echo "\n";
        }
    }
} catch (ApiException $e) {
    echo $e->getMessage() . "\n";
} finally {
    $bigtableClient->close();
    $result = $traceClient->insert($trace);
}
