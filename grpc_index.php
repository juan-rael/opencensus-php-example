<?php

require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Bigtable\V2\BigtableClient;
use OpenCensus\Trace\Integrations\Grpc;
use OpenCensus\Trace\Integrations\Curl;
use Google\Cloud\Trace\TraceClient;
use OpenCensus\Trace\Tracer;
use OpenCensus\Trace\Exporter\FileExporter;
use Google\ApiCore\ApiException;
use OpenCensus\Version;


echo Version::VERSION."\n";

$exporter = new FileExporter( 'logs/grpc_bigtable_' . ( new DateTime() )->format('dmYGisu') . '.json' );
Tracer::start($exporter);
Grpc::load();
Curl::load();

$bigtableClient = new BigtableClient();

$instance_id = 'quickstart-instance-php';
$table_id = 'bigtable-php-table';
try {

    $formattedTableName = Tracer::inSpan( [ 'name' => 'BigTableFormattedName' ] , function($bigtableClient,$instance_id, $table_id){
        return $bigtableClient->tableName(getenv('PROJECT_ID'), $instance_id, $table_id);
    },[$bigtableClient,$instance_id, $table_id]);
    // Read all responses until the stream is complete
    $stream = Tracer::inSpan( [ 'name' => 'BigTableStream' ] , function($bigtableClient,$formattedTableName){
        return $bigtableClient->readRows($formattedTableName);
    },[$bigtableClient,$formattedTableName]);
    Tracer::inSpan( [ 'name' => 'BigTableStreamForeach' ] , function($stream){
        foreach ($stream->readAll() as $element) {
            $iterator = iterator_to_array( $element->getChunks()->getIterator() );
            foreach($iterator as $cellChunk){

                //print_r( get_class_methods( $cellChunk ));
                echo $cellChunk->getRowKey() . ' [ ' . $cellChunk->getValue() . ' ] ( ' . $cellChunk->getTimestampMicros() . ')';
                echo "\n";
            }
        }
    },[$stream]);
} catch(ApiException $e){
    print_r( $e );
} finally {
    $bigtableClient->close();
}
?>