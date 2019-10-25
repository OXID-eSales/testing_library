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
    /**
     * @param string $extension
     * @return string
     */
    public function getReportFileName($extension = 'xml'): string
    {
        return sprintf('report_%s_%s.%s', date('Y-m-d-H-i-s'), uniqid(), $extension);
    }
}
