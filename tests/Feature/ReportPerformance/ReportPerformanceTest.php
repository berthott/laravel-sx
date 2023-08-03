<?php

namespace berthott\SX\Tests\Feature\ReportPerformance;

class ReportPerformanceTest extends ReportPerformanceTestCase
{
    public function test_report_performance(): void
    {
        $startTime = microtime(true);

        $this->get(route('entities.report'))
            ->assertStatus(200);

        $endTime = microtime(true);  
        $executionTime = $endTime - $startTime;
        fwrite(STDERR, print_r("Query took $executionTime seconds to execute.", TRUE));

        $this->assertLessThan(10, $executionTime, 'The report execution time exceeds 10 seconds for 5000 respondents.');
    }
}
