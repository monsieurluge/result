<?php

namespace tests\unit\Result;

use monsieurluge\Result\Result\Combined;
use monsieurluge\Result\Result\Result;
use monsieurluge\Result\Result\Success;
use PHPUnit\Framework\TestCase;

final class CombinedTest extends TestCase
{
    public function testCombinedExists()
    {
        // GIVEN
        $testSubject = new Combined(
            new Success('test'),
            new Success('ok')
        );

        // THEN
        $this->assertInstanceOf(Result::class, $testSubject);
    }
}
