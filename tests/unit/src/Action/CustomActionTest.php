<?php

namespace tests\unit\Action;

use monsieurluge\Result\Action\CustomAction;
use PHPUnit\Framework\TestCase;

final class CustomActionTest extends TestCase
{
    /**
     * @covers monsieurluge\Result\Action\CustomAction::process
     */
    public function testProcessDoesNotMutatesTarget()
    {
        // GIVEN
        $testSubject = [ 'a', 'b', 'c' ];

        $addAnItem = new CustomAction(function(array $target) { $target[] = 'd'; });

        // WHEN
        $addAnItem->process($testSubject);

        // THEN
        $this->assertSame([ 'a', 'b', 'c' ], $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Action\CustomAction::process
     */
    public function testProcessInteractsWithTarget()
    {
        // GIVEN
        $testSubject = new class()
        {
            private $value = 606;
            public function incrementBy(int $step) { $this->value += $step; }
            public function value(): int { return $this->value; }
        };

        $incrementBy60 = new CustomAction(function($target) { $target->incrementBy(60); });

        // WHEN
        $incrementBy60->process($testSubject);

        // THEN
        $this->assertSame(666, $testSubject->value());
    }
}
