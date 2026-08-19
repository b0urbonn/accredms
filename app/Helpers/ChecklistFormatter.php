<?php

namespace App\Helpers;

class ChecklistFormatter
{
    public static function format(?string $text, array $completedItems = [], bool $canToggle = false, int $subfolderId = 0, bool $hasDocuments = true, bool $showBadge = true): string
    {
        if (!$text) {
            return '';
        }

        // Split by newlines or bullet symbols '•'
        $lines = preg_split('/\r\n|\r|\n|•/', $text);
        $rawItems = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $trimmed = ltrim($trimmed, "•\t- *");
            if ($trimmed !== '') {
                $rawItems[] = $trimmed;
            }
        }

        $rawItems = array_unique($rawItems);
        if (empty($rawItems)) {
            return '';
        }

        $total = count($rawItems);
        $completedCount = 0;
        $htmlItems = [];

        foreach ($rawItems as $item) {
            $isChecked = $hasDocuments && in_array($item, $completedItems, true);
            if ($isChecked) {
                $completedCount++;
            }

            $escapedItem = e($item);

            if ($isChecked) {
                $htmlItems[] = '
                    <li class="mb-1 fw-bold fs-8 list-unstyled d-flex align-items-start gap-1 checklist-item-text">
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0 me-1" style="font-size: 0.85rem;" title="Covered by uploaded PDF file"></i>
                        <span class="checklist-item-title">' . $escapedItem . '</span>
                    </li>';
            } else {
                $htmlItems[] = '
                    <li class="mb-1 fw-semibold fs-8 list-unstyled d-flex align-items-start gap-1 checklist-item-text">
                        <i class="bi bi-dash-circle text-warning flex-shrink-0 me-1" style="font-size: 0.85rem;" title="Missing / Not yet covered by uploaded file"></i>
                        <span class="checklist-item-title">' . $escapedItem . '</span>
                    </li>';
            }
        }

        // Header status badge
        $badgeHtml = '';
        if ($showBadge && $total > 0) {
            if ($completedCount >= $total && $hasDocuments) {
                $badgeHtml = '<div class="mb-2"><span class="badge text-bg-success px-2 py-1 fs-8"><i class="bi bi-check-all me-1"></i>All Evidences Complete (' . $completedCount . '/' . $total . ')</span></div>';
            } elseif ($completedCount > 0 && $hasDocuments) {
                $missing = $total - $completedCount;
                $badgeHtml = '<div class="mb-2"><span class="badge text-bg-warning px-2 py-1 fs-8 text-dark"><i class="bi bi-pie-chart-half me-1"></i>Incomplete (' . $completedCount . '/' . $total . ' Covered &middot; ' . $missing . ' Missing)</span></div>';
            } else {
                $badgeHtml = '<div class="mb-2"><span class="badge text-bg-secondary px-2 py-1 fs-8"><i class="bi bi-exclamation-circle me-1"></i>0/' . $total . ' Evidences Covered</span></div>';
            }
        }

        $promptHtml = '';
        if (!$hasDocuments && $canToggle) {
            $promptHtml = '<div class="mt-1 fs-8 text-muted fst-italic opacity-75"><i class="bi bi-info-circle me-1"></i>Upload PDF files and select covered evidences.</div>';
        }

        $listClass = 'checklist-bullet-list mb-0 p-0';
        return $badgeHtml . '<ul class="' . $listClass . '" style="list-style-type: none;">' . implode('', $htmlItems) . '</ul>' . $promptHtml;
    }

    public static function formatForReport(?string $text, array $completedItems = [], bool $hasDocuments = true): string
    {
        if (!$text) {
            return '';
        }

        $lines = preg_split('/\r\n|\r|\n|•/', $text);
        $rawItems = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $trimmed = ltrim($trimmed, "•\t- *");
            if ($trimmed !== '') {
                $rawItems[] = $trimmed;
            }
        }

        $rawItems = array_unique($rawItems);
        if (empty($rawItems)) {
            return '';
        }

        $htmlItems = [];

        foreach ($rawItems as $item) {
            $isChecked = $hasDocuments && in_array($item, $completedItems, true);
            $escapedItem = e($item);

            if ($isChecked) {
                $htmlItems[] = '
                    <li class="mb-1 text-dark fw-bold list-unstyled d-flex align-items-start gap-1" style="line-height: 1.3;">
                        <span class="text-success fw-bold me-1" style="font-size: 0.95rem;">&#10004;</span>
                        <span style="color: #000000 !important;">' . $escapedItem . '</span>
                    </li>';
            } else {
                $htmlItems[] = '
                    <li class="mb-1 text-dark list-unstyled d-flex align-items-start gap-1" style="line-height: 1.3; color: #444444 !important;">
                        <span class="text-secondary fw-bold me-1" style="font-size: 0.95rem;">-</span>
                        <span>' . $escapedItem . '</span>
                    </li>';
            }
        }

        return '<ul class="checklist-report-list mb-0 p-0" style="list-style-type: none;">' . implode('', $htmlItems) . '</ul>';
    }
}

