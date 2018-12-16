<?php

namespace tests\unit\Result;

use Closure;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Combined;
use monsieurluge\Result\Result\CombinedValues;
use monsieurluge\Result\Result\Failure;
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
     * @covers Combined::getValueOrExecOnFailure
     */
    public function testGetValueOfSuccessAndFailureReturnsTheError()
    {
        // GIVEN
        $combined = new Combined(
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure')
            )
        );

        // WHEN
        $testSubject = $combined->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('failure', $testSubject);
    }

    /**
     * @covers Combined::getValueOrExecOnFailure
     */
    public function testGetValueOfFailureAndSuccessReturnsTheError()
    {
        // GIVEN
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
            new Success('test')
        );

        // WHEN
        $testSubject = $combined->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('failure', $testSubject);
    }

    /**
     * @covers Combined::getValueOrExecOnFailure
     */
    public function testGetValueOfFailuresReturnsTheFirstError()
    {
        // GIVEN
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
            new Failure(
                new BaseError('err-4567', 'error')
            )
        );

        // WHEN
        $testSubject = $combined->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('failure', $testSubject);
    }

    /**
     * @covers Combined::getValueOrExecOnFailure
     * @covers Combined::map
     */
    public function testMapSuccessesReturnsTheMappedResult()
    {
        // GIVEN
        $combined = new Combined(
            new Success('test'),
            new Success('ok')
        );

        // WHEN
        $testSubject = $combined
            ->map($this->concatTheStringValues())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('test ok', $testSubject);
    }

    /**
     * Returns the concatenated string values.
     *   ex: 'foo bar' when the first value = 'foo' and the second = 'bar'
     *
     * @return Closure a Closure as follows: f(CombinedValues) -> string
     */
    private function concatTheStringValues(): Closure
    {
        return function (CombinedValues $values) { return sprintf('%s %s', $values->first(), $values->second()); };
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
