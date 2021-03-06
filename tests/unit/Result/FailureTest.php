<?php

namespace tests\unit\Result;

use Closure;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Failure;
use monsieurluge\Result\Result\Success;
use PHPUnit\Framework\TestCase;

final class FailureTest extends TestCase
{

    /**
     * @covers monsieurluge\Result\Result\Failure::getOr
     */
    public function testGetTheError()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN the error's code is fetched
        $code = $failure->getOr($this->extractErrorCode());

        // THEN the code is as expected
        $this->assertSame('err-1234', $code);
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::map
     * @covers monsieurluge\Result\Result\Failure::getOr
     */
    public function testMapDoesNothing()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN a "to uppercase" function is provided and mapped on the result's success
        // AND the code is requested
        $code = $failure
            ->map($this->toUppercase())
            ->getOr($this->extractErrorCode());

        // THEN the code is as expected
        $this->assertSame('err-1234', $code);
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::then
     */
    public function testThenIsNotTriggered()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('err-1234', 'failure'));
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $failure->then(function () use ($counter) { $counter->increment(); return new Success('foo'); });

        // THEN the counter object has not been called
        $this->assertSame(0, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::else
     */
    public function testElseIsTriggered()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('err-1234', 'failure'));
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $failure->else(function () use ($counter) { $counter->increment(); return new Success('baz baz'); });

        // THEN the counter object has been called once
        $this->assertSame(1, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::flatMap
     */
    public function testSuccessfulFlatMapOnFailureDoesNotChangeTheResultingValue()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('err-1234', 'failure'));
        // AND a method which adds 1000 to an int and returns a Result<int>
        $add = function (int $initial) { return new Success($initial + 1000); };

        // WHEN the method is applied, and the resulting value is fetched
        $value = $failure->flatMap($add)->getOr($this->extractErrorCode());

        // THEN the value is as expected
        $this->assertSame('err-1234', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::flatMap
     */
    public function testFailedFlatMapOnFailureDoesNotChangeTheResultingValue()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('err-1234', 'failure'));
        // AND a method which returns an Error
        $fail = function (int $initial) { return new Failure(new BaseError('fail', 'qwerty')); };

        // WHEN the method is applied, and the resulting value is fetched
        $errorCode = $failure->flatMap($fail)->getOr($this->extractErrorCode());

        // THEN the error code is as expected (the initial one)
        $this->assertSame('err-1234', $errorCode);
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::join
     */
    public function testCanJoinFailures()
    {
        // GIVEN two failed results
        $failure1 = new Failure(new BaseError('fail-1', 'failure 1'));
        $failure2 = new Failure(new BaseError('fail-2', 'failure 2'));

        // WHEN the results are combined and the resulting value is fetched
        $errorCode = $failure1->join($failure2)->getOr($this->extractErrorCode());

        // THEN the resulting value is as expected -> the first failure error code
        $this->assertSame('fail-1', $errorCode);
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::join
     */
    public function testCanJoinFailureAndSuccess()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('fail', 'failure'));
        // AND a successful result
        $success = new Success(666);

        // WHEN the results are combined and the resulting value is fetched
        $errorCode = $failure->join($success)->getOr($this->extractErrorCode());

        // THEN the resulting value is as expected -> the failure error code
        $this->assertSame('fail', $errorCode);
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
        return new class ()
        {
            private $count = 0;
            public function increment() { $this->count++; }
            public function total() { return $this->count; }
        };
    }

    /**
     * Returns a function which extracts the error's code.
     *
     * @return Closure the function as follows: Error -> string
     */
    private function extractErrorCode(): Closure
    {
        return function (Error $error): string
        {
            return $error->code();
        };
    }

    /**
     * Returns a function which takes a text and returns its uppercase version.
     *
     * @return Closure the function as follows: string -> string
     */
    private function toUppercase(): Closure
    {
        return function (string $text): string
        {
            return strtoupper($text);
        };
    }

    /**
     * Returns a function which replaces an error's code and returns a new Error.
     *
     * @param string $replacement
     *
     * @return Closure the function as follows: Error -> Error
     */
    private function replaceErrorCodeWith(string $replacement): Closure
    {
        return function (Error $origin) use ($replacement): Error
        {
            return new BaseError($replacement, $origin->message());
        };
    }

}
