<?php

/**
 * @file tests/jobs/statistics/CompileUniqueRequestsTest.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Tests for compile unique requests job.
 */

namespace APP\tests\jobs\statistics;

use APP\jobs\statistics\CompileUniqueRequests;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PKP\db\DAORegistry;
use PKP\tests\PKPTestCase;

#[RunTestsInSeparateProcesses]
#[CoversClass(CompileUniqueRequests::class)]
class CompileUniqueRequestsTest extends PKPTestCase
{
    /**
     * base64_encoded serializion from OJS 3.4.0
     */
    protected string $serializedJobData = <<<END
    O:41:"APP\jobs\statistics\CompileUniqueRequests":3:{s:9:"\0*\0loadId";s:25:"usage_events_20240130.log";s:10:"connection";s:8:"database";s:5:"queue";s:5:"queue";}
    END;

    /**
     * Test job is a proper instance
     */
    public function testUnserializationGetProperDepositIssueJobInstance(): void
    {
        $this->assertInstanceOf(
            CompileUniqueRequests::class,
            unserialize($this->serializedJobData)
        );
    }

    /**
     * Ensure that a serialized job can be unserialized and executed
     */
    public function testRunSerializedJob(): void
    {
        /** @var CompileUniqueRequests $compileUniqueRequestsJob */
        $compileUniqueRequestsJob = unserialize($this->serializedJobData);

        $temporaryItemRequestsDAOMock = Mockery::mock(\APP\statistics\TemporaryItemRequestsDAO::class)
            ->makePartial()
            ->shouldReceive([
                'compileBookItemUniqueClicks' => null,
                'compileChapterItemUniqueClicks' => null,
            ])
            ->withAnyArgs()
            ->getMock();

        DAORegistry::registerDAO('TemporaryItemRequestsDAO', $temporaryItemRequestsDAOMock);

        $temporaryTitleRequestsDAOMock = Mockery::mock(\APP\statistics\TemporaryTitleRequestsDAO::class)
            ->makePartial()
            ->shouldReceive([
                'compileTitleUniqueClicks' => null,
            ])
            ->withAnyArgs()
            ->getMock();

        DAORegistry::registerDAO('TemporaryTitleRequestsDAO', $temporaryTitleRequestsDAOMock);

        $compileUniqueRequestsJob->handle();

        $this->expectNotToPerformAssertions();
    }
}
