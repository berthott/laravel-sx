<?php

namespace berthott\SX\Services;

class SxReportPdfService
{
    /**
     * Build a report for the given class.
     */
    public function estimatePages(array $pages): int
    {
        // assumptions basing on default css values, a4, portrait
        $textQuestionHeight = 25;
        $textAnswerHeight = 17;
        $pageHeight = 842;

        $pageCount = 0;
        foreach ($pages as $group) {
            $groupHeight = 0;
            foreach ($group['data'] as $question) {
                if ($question['type'] === 'base64') {
                    $groupHeight += $question['dimensions']['height'];
                }
                if ($question['type'] === 'html') {
                    $html = $question['result'];
                    $answersCount = substr_count($html, '</li>');
                    $groupHeight += $textQuestionHeight + $answersCount * $textAnswerHeight;
                }
            }
            $pageCount += ceil($groupHeight / $pageHeight);
        }
        return $pageCount;
    }
}
