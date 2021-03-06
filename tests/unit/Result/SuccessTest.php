<?php

namespace tests\unit\Result;

use Closure;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Failure;
use monsieurluge\Result\Result\Success;
use PHPUnit\Framework\TestCase;

final class SuccessTest extends TestCase
{

    /**
     * @covers monsieurluge\Result\Result\Success::getOr
     */
    public function testGetTheValue()
    {
        // GIVEN a successful result
        $success = new Success('foo bar');

        // WHEN the value is requested
        $value = $success->getOr($this->extractErrorCode());

        // THEN the value is the one used to create the result object
        $this->assertSame('foo bar', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::map
     * @covers monsieurluge\Result\Result\Success::getOr
     */
    public function testMapChangeTheResultValue()
    {
        // GIVEN a successful result
        $success = new Success('foo bar');

        // WHEN a "to uppercase" function is provided and mapped on the result's success
        // AND the value is requested
        $value = $success
            ->map($this->toUppercase())
            ->getOr($this->extractErrorCode());

        // THEN the value is the uppercase version of the original one
        $this->assertSame('FOO BAR', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::then
     */
    public function testThenIsTriggered()
    {
        // GIVEN a successful result
        $success = new Success('foo bar');
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $success->then(function () use ($counter) { $counter->increment(); });

        // THEN the counter object has been called once
        $this->assertSame(1, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Success::then
     */
    public function testThenDoesNotChangeTheResult()
    {
        // GIVEN a successful result
        $success = new Success('foo bar');
        // AND an action which appends "OK" to a text
        $append = function (string $text) { return sprintf('OK %s', $text); };

        // WHEN an action is provided and the resulting value is fetched
        $value = $success->then($append)->getOr($this->extractErrorCode());

        // THEN the value is the same as the origin
        $this->assertSame('foo bar', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::then
     */
    public function testThenCanInteractWithTheResultValue()
    {
        // GIVEN a successful result, which is a Counter object startig at 0
        $success = new Success($this->createCounter());
        // AND an action which increments a counter
        $incrementTwoTimes = function ($counter) { $counter->increment(); $counter->increment(); };

        // WHEN an action is provided and the total is fetched
        $total = $success
            ->then($incrementTwoTimes)
            ->getOr($this->extractErrorCode())
            ->total();

        // THEN the total is as expected
        $this->assertSame(2, $total);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::else
     */
    public function testElseIsNotTriggered()
    {
        // GIVEN the successful result
        $success = new Success(1234);
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $success->else(function () use ($counter) { $counter->increment(); return new Success('baz baz'); });

        // THEN the counter object has not been called
        $this->assertSame(0, $counter->total());
    }

    /**
     * @covers monsieurluge\Result\Result\Success::flatMap
     */
    public function testSuccessfulFlatMapOnSuccessChangesTheResultingValue()
    {
        // GIVEN a successful result
        $success = new Success(1234);
        // AND a method which adds 1000 to an int and returns a Result<int>
        $add = function (int $initial) { return new Success($initial + 1000); };

        // WHEN the method is applied, and the resulting value is fetched
        $value = $success->flatMap($add)->getOr($this->extractErrorCode());

        // THEN the value is as expected
        $this->assertSame(2234, $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::flatMap
     */
    public function testFailedFlatMapOnSuccessChangesTheResultingValue()
    {
        // GIVEN a successful result
        $success = new Success(1234);
        // AND a method which returns a failure
        $fail = function (int $initial) { return new Failure(new BaseError('fail', sprintf('was %s', $initial))); };

        // WHEN the method is applied, and the resulting error is fetched
        $errorCode = $success->flatMap($fail)->getOr($this->extractErrorCode());

        // THEN the error is as expected
        $this->assertSame('fail', $errorCode);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::join
     */
    public function testCanJoinSuccesses()
    {
        // GIVEN two successful results
        $success1 = new Success(666);
        $success2 = new Success(333);

        // WHEN the results are combined and the resulting value is fetched
        $value = $success1->join($success2)->getOr($this->extractErrorCode());

        // THEN the resulting value is as expected
        $this->assertSame([ 666, 333 ], $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::join
     */
    public function testCanJoinSuccessAndFailure()
    {
        // GIVEN one successful result
        $success = new Success(666);
        // AND a failed one
        $failure = new Failure(new BaseError('fail', 'failure'));

        // WHEN the results are combined and the resulting value is fetched
        $errorCode = $success->join($failure)->getOr($this->extractErrorCode());

        // THEN the resulting value is as expected
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
