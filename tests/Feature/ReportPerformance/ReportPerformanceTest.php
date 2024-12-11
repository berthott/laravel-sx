<?php

namespace berthott\SX\Tests\Feature\ReportPerformance;

use Facades\berthott\SX\Helpers\SxHelpers;
use Illuminate\Support\Facades\DB;

class ReportPerformanceTest extends ReportPerformanceTestCase
{
    public function test_report_performance_5000(): void
    {
        $respondents = DB::table('entities')->get()->take(5000)->pluck('fake')->toArray();

        $executionTime = SxHelpers::logAndGetExecutionTime('Querying 5000 respondents.', function () use ($respondents) {
            $this->get(route('entities.report', [
                'filter' => [
                    'fake' => join(',', $respondents),
                ]
            ]))
                ->assertStatus(200);
        });

        $this->assertLessThan(2, $executionTime, 'The report execution time exceeds 2 seconds for 5000 respondents.');
    }

    public function test_report_performance_10000(): void
    {
        $respondents = DB::table('entities')->get()->take(10000)->pluck('fake')->toArray();

        $executionTime = SxHelpers::logAndGetExecutionTime('Querying 10000 respondents.', function () use ($respondents) {
            $this->get(route('entities.report', [
                'filter' => [
                    'fake' => join(',', $respondents),
                ]
            ]))
                ->assertStatus(200);
        });

        $this->assertLessThan(4, $executionTime, 'The report execution time exceeds 4 seconds for 10000 respondents.');
    }

    public function test_report_performance_50000(): void
    {
        $respondents = DB::table('entities')->get()->take(50000)->pluck('fake')->toArray();

        $executionTime = SxHelpers::logAndGetExecutionTime('Querying 50000 respondents.', function () use ($respondents) {
            $this->get(route('entities.report', [
                'filter' => [
                    'fake' => join(',', $respondents),
                ]
            ]))
                ->assertStatus(200);
        });

        $this->assertLessThan(15, $executionTime, 'The report execution time exceeds 12 seconds for 50000 respondents.');
    }
}
