<?php

namespace tests\unit\Result;

use Closure;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Combined;
use monsieurluge\Result\Result\Result;
use monsieurluge\Result\Result\Success;
use PHPUnit\Framework\TestCase;

final class CombinedTest extends TestCase
{
    /**
     * @covers Combined::getValueOrExecOnFailure
     */
    public function testGetValueOfSuccessesReturnsCombinedValues()
    {
        // GIVEN
        $combined = new Combined(
            new Success('test'),
            new Success('ok')
        );

        // WHEN
        $testSubject = $combined->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('test', $testSubject->first());

        $this->assertSame('ok', $testSubject->second());
    }

    /**
     * Returns a Closure: f(Error) -> string
     *
     * @return Closure
     */
    private function returnErrorMessage(): Closure
    {
        return function (Error $error) { return $error->message(); };
    }
}
