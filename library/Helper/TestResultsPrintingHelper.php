<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\TestingLibrary\Helper;

/**
 * @internal
 */
class TestResultsPrintingHelper
{
    private const TIMESTAMP_FORMAT = 'Y-m-d-H-i-s';
    private const TIMESTAMP_INSERTION_MARKER = 'TIMESTAMP';

    public function getReportFileName(string $extension = 'xml'): string
    {
        return sprintf('report_%s.%s', $this->getUniqueTimestamp(), $extension);
    }

    public function insertReportTimestamps(string $command): string
    {
        return str_replace(self::TIMESTAMP_INSERTION_MARKER, $this->getUniqueTimestamp(), $command);
    }

    private function getUniqueTimestamp(): string
    {
        return sprintf('%s_%s', date(self::TIMESTAMP_FORMAT), uniqid());
    }
}
