<?php

namespace tests\integration\Result;

use Closure;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Combined;
use monsieurluge\Result\Result\Failure;
use monsieurluge\Result\Result\Success;
use PHPUnit\Framework\TestCase;

final class CombinedTest extends TestCase
{
    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     */
    public function testGetTheValueOfSuccessesReturnsCombinedValues()
    {
        // GIVEN combined successes
        $combined = new Combined([ new Success('test'), new Success('ok'), new Success('!!') ]);

        // WHEN the value is requested
        $value = $combined->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the value is an array containing the successes values, order unchanged
        $this->assertSame([ 'test', 'ok', '!!' ], $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     */
    public function testGetTheValueOfSuccessesAndFailuresReturnsTheFirstError()
    {
        // GIVEN combined successes and failure
        $combined = new Combined([
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure one')
            ),
            new Success('!!'),
            new Failure(
                new BaseError('err-4567', 'failure two')
            )
        ]);

        // WHEN the value is requested
        $value = $combined->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the value is the failure error's code
        $this->assertSame('err-1234', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::map
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     */
    public function testCanMapOnSuccesses()
    {
        // GIVEN combined successes
        $combined = new Combined([ new Success('test'), new Success('ok'), new Success('!!') ]);

        // WHEN the result values are requested to be concatenated
        // AND the resulting value is requested
        $value = $combined
            ->map($this->concatenateTexts())
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the value is the concatenation of the successes values
        $this->assertSame('test ok !!', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::map
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     */
    public function testCannotMapWhenThereIsAtLeastOneFailure()
    {
        // GIVEN combined success and failure
        $combined = new Combined([
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure one')
            ),
            new Success('!!'),
            new Failure(
                new BaseError('err-4567', 'failure two')
            )
        ]);

        // WHEN the result values are requested to be concatenated
        // AND the value is requested
        $value = $combined
            ->map($this->concatenateTexts())
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the value is the failure error's code
        $this->assertSame('err-1234', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::mapOnFailure
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     */
    public function testMapOnFailureWithSuccessesDoesNothing()
    {
        // GIVEN combined successes
        $combined = new Combined([ new Success('test'), new Success('ok'), new Success('!!') ]);

        // WHEN a "append to error's code" function is provided and mapped on the result's failure
        // AND the value is requested
        $value = $combined
            ->mapOnFailure($this->appendToErrorCode(' KO'))
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the value is an array containing the successes values, order unchanged
        $this->assertSame([ 'test', 'ok', '!!' ], $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::mapOnFailure
     * @covers monsieurluge\Result\Result\Combined::getValueOrExecOnFailure
     */
    public function testMapOnFailureWithSuccessesAndFailuresChangesTheFirstError()
    {
        // GIVEN combined success and failure
        $combined = new Combined([
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure one')
            ),
            new Success('!!'),
            new Failure(
                new BaseError('err-4567', 'failure two')
            )
        ]);

        // WHEN a "append to error's code" function is provided and mapped on the result's failure
        // AND the value is requested
        $value = $combined
            ->mapOnFailure($this->appendToErrorCode(' KO'))
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the value is the extended error's code
        $this->assertSame('err-1234 KO', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::then
     */
    public function testSuccessesTriggersTheThenAction()
    {
        // GIVEN two combined successes
        $combined = new Combined([ new Success('test'), new Success('ok'), new Success('!!') ]);
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided, which increments the counter
        $combined->then(function () use ($counter) { $counter->increment(); return new Success('foo'); });

        // THEN the counter object has been called once
        $this->assertSame(1, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::then
     */
    public function testSuccessAndFailureDoesNotTriggerTheThenAction()
    {
        // GIVEN a success and failure combination
        $combined = new Combined([
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure one')
            ),
            new Success('!!'),
            new Failure(
                new BaseError('err-4567', 'failure two')
            )
        ]);
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $combined->then(function () use ($counter) { $counter->increment(); return new Success('foo'); });

        // THEN the counter object has not been called
        $this->assertSame(0, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::else
     */
    public function testSuccessesDoesNotTriggerTheElseMethod()
    {
        // GIVEN a successes combination
        $combined = new Combined(
            new Success('test'),
            new Success('ok')
        );
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $combined->else(function () use ($counter) { $counter->increment(); return new Success('baz baz'); });

        // THEN the counter object has not been called
        $this->assertSame(0, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::else
     */
    public function testSuccessAndFailureTriggersTheElseMethod()
    {
        // GIVEN a success and failure combination
        $combined = new Combined(
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure')
            )
        );
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $combined->else(function () use ($counter) { $counter->increment(); return new Success('baz baz'); });

        // THEN the counter object has been called once
        $this->assertSame(1, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::else
     */
    public function testFailureAndSuccessTriggersTheElseMethod()
    {
        // GIVEN a failure and success combination
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
            new Success('test')
        );
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $combined->else(function () use ($counter) { $counter->increment(); return new Success('baz baz'); });

        // THEN the counter object has been called once
        $this->assertSame(1, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::else
     */
    public function testFailuresTriggersTheElseMethod()
    {
        // GIVEN a failures combination
        $combined = new Combined(
            new Failure(
                new BaseError('err-1234', 'failure one')
            ),
            new Failure(
                new BaseError('err-5678', 'failure two')
            )
        );
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $combined->else(function () use ($counter) { $counter->increment(); return new Success('baz baz'); });

        // THEN the counter object has been called once
        $this->assertSame(1, $counter->total());
    }

    /**
     * Returns a function which concatenates an array of text.
     *
     * @return Closure the function as follows: string[] -> string
     */
    private function concatenateTexts(): Closure
    {
        return function (array $texts): string {
            return implode(' ', $texts);
        };
    }

    /**
     * Creates a "counter" object who exposes the following methods:
     *  - increment: () -> void
     *  - total: () -> int
     *
     * @return object
     */
    private function createCounter(): object
    {
        return new class () {
            private $count = 0;
            public function increment() { $this->count++; }
            public function total() { return $this->count; }
        };
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
     * Returns a function which appends a suffix to an error's code and returns a new Error.
     *
     * @param string $suffix
     *
     * @return Closure the function as follows: Error -> Error
     */
    private function appendToErrorCode(string $suffix): Closure
    {
        return function (Error $origin) use ($suffix): Error
        {
            return new BaseError(
                $origin->code() . $suffix,
                $origin->message()
            );
        };
    }
}
