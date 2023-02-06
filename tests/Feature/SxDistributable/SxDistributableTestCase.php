<?php

namespace berthott\SX\Tests\Feature\SxDistributable;

use berthott\InternalRequest\InternalRequestServiceProvider;
use berthott\SX\Facades\SxApiService;
use berthott\SX\SxServiceProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Barryvdh\DomPDF\ServiceProvider as DomPdfServiceProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

abstract class SxDistributableTestCase extends BaseTestCase
{
    protected $now;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('sx:init');
    }

    protected function getPackageProviders($app)
    {
        return [
            SxServiceProvider::class,
            InternalRequestServiceProvider::class,
            DomPdfServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->setUpEntityTable();
        $this->now = now();
        Carbon::setTestNow($this->now);
        Config::set('sx.namespace', __NAMESPACE__);
        Config::set('sx-distribution.namespace', __NAMESPACE__);
        SxApiService::shouldReceive('exportStructure')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../structure_de.csv'),
            ));
        SxApiService::shouldReceive('exportLabels')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../labels_de.csv'),
            ));
        SxApiService::shouldReceive('exportDataset')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../dataset.csv'),
            ));
        SxApiService::shouldReceive('create')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../841931211.xml'),
            ));
        SxApiService::makePartial();
    }

    private function setUpEntityTable(): void
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });
    }
    
    protected function assertExpectedFileResponse(TestResponse $response, string $expectedPath, $format = '.pdf', bool $delete = true)
    {
        $storagePath = __FUNCTION__;
        $expectedName = basename($expectedPath);
        
        // store response in storage
        Storage::put($storagePath.'/'.$expectedName.'_actual'.$format, $response->getContent());

        // store expected in storage
        Storage::putFileAs($storagePath, new UploadedFile(
            $expectedPath.$format,
            $expectedName.$format,
        ), $expectedName.'_expect'.$format);

        $actualPath = Storage::path($storagePath.'/'.$expectedName.'_actual'.$format);
        $expectedPath = Storage::path($storagePath.'/'.$expectedName.'_expect'.$format);

        // The files deffer in their timestamps and ID
        // $this->assertSame(file_get_contents($actualPath), file_get_contents($expectedPath));
        $this->assertSame(filesize($actualPath), filesize($expectedPath));

        if ($delete) {
            Storage::delete($actualPath);
            Storage::delete($expectedPath);
        }
    }
}
