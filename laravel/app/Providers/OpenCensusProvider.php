<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenCensus\Trace\Exporter\FileExporter;
use OpenCensus\Trace\Tracer;
use OpenCensus\Trace\Integrations\Laravel;
use OpenCensus\Trace\Integrations\Mysql;
use OpenCensus\Trace\Integrations\PDO;
use DateTime;
class OpenCensusProvider extends ServiceProvider
{
    public function boot()
    {
        if (php_sapi_name() == 'cli') {
            return;
        }

        // Enable OpenCensus extension integrations
        Laravel::load();
        Mysql::load();
        PDO::load();

        // Start the request tracing for this request
        $exporter = new FileExporter( storage_path().'/opencensus/file' . ( new DateTime() )->format('dmYGisu') . '.json' );
        Tracer::start($exporter);

        // Create a span that starts from when Laravel first boots (public/index.php)
        Tracer::inSpan(['name' => 'bootstrap', 'startTime' => LARAVEL_START], function () {});
    }
}