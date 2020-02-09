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
     * @covers monsieurluge\Result\Result\Combined::getOr
     */
    public function testGetTheValueOfSuccessesReturnsCombinedValues()
    {
        // GIVEN combined successes
        $combined = new Combined([ new Success('test'), new Success('ok'), new Success('!!') ]);

        // WHEN the value is requested
        $value = $combined->getOr($this->extractErrorCode());

        // THEN the value is an array containing the successes values, order unchanged
        $this->assertSame([ 'test', 'ok', '!!' ], $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::getOr
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
        $value = $combined->getOr($this->extractErrorCode());

        // THEN the value is the failure error's code
        $this->assertSame('err-1234', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::map
     * @covers monsieurluge\Result\Result\Combined::getOr
     */
    public function testCanMapOnSuccesses()
    {
        // GIVEN combined successes
        $combined = new Combined([ new Success('test'), new Success('ok'), new Success('!!') ]);
        // AND a function which concatenates tree texts
        $concatenate = function (string $text1, string $text2, string $text3) {
            return sprintf('%s %s %s', $text1, $text2, $text3);
        };

        // WHEN the result values are requested to be concatenated and the resulting value is fetched
        $value = $combined->map($concatenate)->getOr($this->extractErrorCode());

        // THEN the value is the concatenation of the successes values
        $this->assertSame('test ok !!', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::map
     * @covers monsieurluge\Result\Result\Combined::getOr
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
            ->getOr($this->extractErrorCode());

        // THEN the value is the failure error's code
        $this->assertSame('err-1234', $value);
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

        // WHEN an action is provided, which increments the counter
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
        $combined = new Combined([ new Success('test'), new Success('ok'), new Success('!!') ]);
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided, which increments the counter
        $combined->else(function () use ($counter) { $counter->increment(); return new Success('baz baz'); });

        // THEN the counter object has not been called
        $this->assertSame(0, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::else
     */
    public function testSuccessesAndFailuresTriggersTheElseMethod()
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
        $combined->else(function () use ($counter) { $counter->increment(); return new Success('baz baz'); });

        // THEN the counter object has been called once
        $this->assertSame(1, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::flatMap
     */
    public function testSuccessfulFlatMapOnSuccessesChangesTheResultingValue()
    {
        // GIVEN a successes combination
        $combined = new Combined([ new Success('test'), new Success('ok') ]);
        // AND a method which contatenates the texts
        $concatenate = function (string $text1, string $text2) {
            return new Success(sprintf('%s %s', $text1, $text2));
        };

        // WHEN the action is applied, and the values are fetched
        $values = $combined->flatMap($concatenate)->getOr($this->extractErrorCode());

        // THEN the values are as expected
        $this->assertSame('test ok', $values);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::flatMap
     */
    public function testFailedFlatMapOnSuccessesReturnsFailure()
    {
        // GIVEN a successes combination
        $combined = new Combined([ new Success('test'), new Success('ok') ]);
        // AND a method which returns a Failure
        $fail = function () { return new Failure(new BaseError('fail', 'qwerty')); };

        // WHEN the action is applied, and the error code is fetched
        $errorCode = $combined->flatMap($fail)->getOr($this->extractErrorCode());

        // THEN the values are as expected
        $this->assertSame('fail', $errorCode);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::flatMap
     */
    public function testFlatMapIsNotAppliedIfThereIsAtLeastOneFailure()
    {
        // GIVEN a success and failure combination
        $combined = new Combined([
            new Success('test'),
            new Failure(
                new BaseError('err-1234', 'failure')
            ),
        ]);
        // AND a method which returns an int
        $concatenate = function (array $results) { return new Success(666); };

        // WHEN the action is called, and the error code is fetched
        $errorCode = $combined->flatMap($concatenate)->getOr($this->extractErrorCode());

        // THEN the error code is as expected
        $this->assertSame('err-1234', $errorCode);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::join
     */
    public function testCanJoinCombinedResultsWithSuccess()
    {
        // GIVEN a successes combination
        $combined = new Combined([ new Success(666), new Success(333) ]);
        // AND another successful result
        $success = new Success(1);

        // WHEN the results are joined, and the resulting value is fetched
        $value = $combined->join($success)->getOr($this->extractErrorCode());

        // THEN the value is as expected
        $this->assertSame([ 666, 333, 1 ], $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::join
     */
    public function testCanJoinCombinedResultsWithFailure()
    {
        // GIVEN a successes combination
        $combined = new Combined([ new Success(666), new Success(333) ]);
        // AND another failed result
        $failure = new Failure(new BaseError('fail', 'failure'));

        // WHEN the results are joined, and the resulting value is fetched
        $errorCode = $combined->join($failure)->getOr($this->extractErrorCode());

        // THEN the value is as expected -> the failure error code
        $this->assertSame('fail', $errorCode);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::join
     */
    public function testCanJoinCombinedSuccessAnfFailureWithSuccess()
    {
        // GIVEN a success and failure combination
        $combined = new Combined([ new Success(666), new Failure(new BaseError('fail', 'failure')) ]);
        // AND another successful result
        $success = new Success(1);

        // WHEN the results are joined, and the resulting value is fetched
        $errorCode = $combined->join($success)->getOr($this->extractErrorCode());

        // THEN the value is as expected -> the first failure error code
        $this->assertSame('fail', $errorCode);
    }

    /**
     * @covers monsieurluge\Result\Result\Combined::join
     */
    public function testCanJoinCombinedFailureAnfSuccessWithFailure()
    {
        // GIVEN a success and failure combination
        $combined = new Combined([ new Failure(new BaseError('fail-1', 'failure')), new Success(666) ]);
        // AND another failed result
        $failure = new Failure(new BaseError('fail-2', 'failure'));

        // WHEN the results are joined, and the resulting value is fetched
        $errorCode = $combined->join($failure)->getOr($this->extractErrorCode());

        // THEN the value is as expected -> the first failure error code
        $this->assertSame('fail-1', $errorCode);
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
