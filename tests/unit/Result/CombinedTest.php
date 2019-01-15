<?php

namespace tests\unit\Result;

use Closure;
use monsieurluge\Result\Action\Action;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\BaseCombinedValues;
use monsieurluge\Result\Result\Combined;
use monsieurluge\Result\Result\CombinedValues;
use monsieurluge\Result\Result\Failure;
use monsieurluge\Result\Result\Result;
use monsieurluge\Result\Result\Success;
use PHPUnit\Framework\TestCase;

final class CombinedTest extends TestCase
{

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
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
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
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
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
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
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
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
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::map
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
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::map
     */
    public function testMapSuccessAndFailureReturnsFailure()
    {
        // GIVEN
        $combined = new Combined(
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure')
            )
        );

        // WHEN
        $testSubject = $combined
            ->map($this->concatTheStringValues())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('failure', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::map
     */
    public function testMapFailureAndSuccessReturnsFailure()
    {
        // GIVEN
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
            new Success('test')
        );

        // WHEN
        $testSubject = $combined
            ->map($this->concatTheStringValues())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('failure', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::map
     */
    public function testMapFailuresReturnsTheFirstFailure()
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
        $testSubject = $combined
            ->map($this->concatTheStringValues())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('failure', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::mapOnFailure
     */
    public function testMapOnFailureWithSuccessesDoesNothing()
    {
        // GIVEN
        $combined = new Combined(
            new Success('test'),
            new Success('ok')
        );

        $expexted = new BaseCombinedValues('test', 'ok');

        // WHEN
        $testSubject = $combined
            ->mapOnFailure($this->addPrefixToErrorMessage())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertEquals($expexted, $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::mapOnFailure
     */
    public function testMapOnFailureWithSuccessAndFailureMapsTheError()
    {
        // GIVEN
        $combined = new Combined(
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure')
            )
        );

        // WHEN
        $testSubject = $combined
            ->mapOnFailure($this->addPrefixToErrorMessage())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertEquals('[KO] failure', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::mapOnFailure
     */
    public function testMapOnFailureWithFailureAndSuccessMapsTheError()
    {
        // GIVEN
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
            new Success('test')
        );

        // WHEN
        $testSubject = $combined
            ->mapOnFailure($this->addPrefixToErrorMessage())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertEquals('[KO] failure', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::mapOnFailure
     */
    public function testMapOnFailureWithFailuresMapsTheFirstError()
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
        $testSubject = $combined
            ->mapOnFailure($this->addPrefixToErrorMessage())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertEquals('[KO] failure', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::then
     */
    public function testSuccessesTriggersTheThenAction()
    {
        // GIVEN
        $combined = new Combined(
            new Success('test'),
            new Success('ok')
        );

        // WHEN
        $testSubject = $combined
            ->then($this->actionConcatTheStringValues())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('test ok', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::then
     */
    public function testSuccessAndFailureDoesNotTriggerTheThenActionAndReturnsAnError()
    {
        // GIVEN
        $combined = new Combined(
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure')
            )
        );

        // WHEN
        $testSubject = $combined
            ->then($this->actionConcatTheStringValues())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('failure', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::then
     */
    public function testFailureAndSuccessDoesNotTriggerTheThenActionAndReturnsAnError()
    {
        // GIVEN
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
            new Success('test')
        );

        // WHEN
        $testSubject = $combined
            ->then($this->actionConcatTheStringValues())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('failure', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     * @covers monsieurluge\Result\Result\Combined::then
     */
    public function testFailuresDoesNotTriggerTheThenActionAndReturnsTheFirstError()
    {
        // GIVEN
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
            new Failure(
                new BaseError('err-5678', 'error')
            )
        );

        // WHEN
        $testSubject = $combined
            ->then($this->actionConcatTheStringValues())
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('failure', $testSubject);
    }

    /**
     * Returns a Closure which adds the "[KO]" prefix to an error message.
     *
     * @return Closure a Closure as follows: f(Error) -> Error
     */
    private function addPrefixToErrorMessage(): Closure
    {
        return function (Error $error) {
            return new BaseError(
                $error->code(),
                sprintf('[KO] %s', $error->message())
            );
        };
    }

    /**
     * Returns an Action which concatenate two string values.
     *   ex: returns "foo bar" when the first value = "foo" and the second = "bar"
     *
     * @return Action
     */
    private function actionConcatTheStringValues(): Action
    {
        return new class() implements Action
        {
            public function process($target): Result
            {
                return new Success(
                    sprintf('%s %s', $target->first(), $target->second())
                );
            }
        };
    }

    /**
     * Returns a function which concatenate two string values.
     *   ex: returns "foo bar" when the first value = "foo" and the second = "bar"
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
