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
     * @covers monsieurluge\Result\Result\Combined::else
     */
    public function testSuccessesDoesNotTriggerTheElseMethod()
    {
        // GIVEN a success-success combination
        $combined = new Combined(
            new Success('test'),
            new Success('ok')
        );
        // AND a class which is used to store a text
        $storage = new class () {
            public $text = 'nothing';
            public function store (string $text) { $this->text = $text; }
        };

        // WHEN the else method is sent, and the value is fetched
        $value = $combined
            ->else(function (Error $error) use ($storage) { $storage->store($error->code()); })
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the combined values have not been altered
        $this->assertSame('test', $value->first());
        $this->assertSame('ok', $value->second());
        // AND the stored text has not been altered
        $this->assertSame('nothing', $storage->text);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::else
     */
    public function testSuccessAndFailureTriggersTheElseMethod()
    {
        // GIVEN a success-success combination
        $combined = new Combined(
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure')
            )
        );
        // AND a class which is used to store a text
        $storage = new class () {
            public $text = 'nothing';
            public function store (string $text) { $this->text = $text; }
        };

        // WHEN the else method is sent, and the error's code is fetched
        $code = $combined
            ->else(function (Error $error) use ($storage) { $storage->store($error->code()); })
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the error's code was fetched
        $this->assertSame('err-1234', $code);
        // AND the stored text was updated using the error's code
        $this->assertSame('err-1234', $storage->text);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::else
     */
    public function testFailureAndSuccessTriggersTheElseMethod()
    {
        // GIVEN a success-success combination
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
            new Success('test')
        );
        // AND a class which is used to store a text
        $storage = new class () {
            public $text = 'nothing';
            public function store (string $text) { $this->text = $text; }
        };

        // WHEN the else method is sent, and the error's code is fetched
        $code = $combined
            ->else(function (Error $error) use ($storage) { $storage->store($error->code()); })
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the error's code was fetched
        $this->assertSame('err-1234', $code);
        // AND the stored text was updated using the error's code
        $this->assertSame('err-1234', $storage->text);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::else
     */
    public function testFailuresTriggersTheElseMethodProvidingTheFirstError()
    {
        // GIVEN a success-success combination
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
            new Failure(
                new BaseError('err-5678', 'error')
            )
        );
        // AND a class which is used to store a text
        $storage = new class () {
            public $text = 'nothing';
            public function store (string $text) { $this->text = $text; }
        };

        // WHEN the else method is sent, and the error's code is fetched
        $code = $combined
            ->else(function (Error $error) use ($storage) { $storage->store($error->code()); })
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the error's code was fetched
        $this->assertSame('err-1234', $code);
        // AND the stored text was updated using the error's code
        $this->assertSame('err-1234', $storage->text);
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
     * Returns a function which extracts the error's code.
     *
     * @return Closure the function as follows: f(Error) -> string
     */
    private function extractErrorCode(): Closure
    {
        return function (Error $error) {
            return $error->code();
        };
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
