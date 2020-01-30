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
     * @covers monsieurluge\Result\Result\Success::getValueOrExecOnFailure
     */
    public function testGetTheValue()
    {
        // GIVEN a successful result
        $success = new Success('foo bar');

        // WHEN the value is requested
        $value = $success->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the value is the one used to create the result object
        $this->assertSame('foo bar', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::map
     * @covers monsieurluge\Result\Result\Success::getValueOrExecOnFailure
     */
    public function testMapChangeTheResultValue()
    {
        // GIVEN a successful result
        $success = new Success('foo bar');

        // WHEN a "to uppercase" function is provided and mapped on the result's success
        // AND the value is requested
        $value = $success
            ->map($this->toUppercase())
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the value is the uppercase version of the original one
        $this->assertSame('FOO BAR', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::mapOnFailure
     * @covers monsieurluge\Result\Result\Success::getValueOrExecOnFailure
     */
    public function testMapOnFailureDoesNothing()
    {
        // GIVEN a successful result
        $success = new Success('foo bar');

        // WHEN a "replace error's code" function is provided and mapped on the result's failure
        // AND the value is requested
        $value = $success
            ->mapOnFailure($this->replaceErrorCodeWith('err-666'))
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the initial value has not been altered
        $this->assertSame('foo bar', $value);
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
        $success->then(function () use ($counter) { $counter->increment(); return new Success('baz baz'); });

        // THEN the counter object has been called once
        $this->assertSame(1, $counter->total());
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
        $value = $success->flatMap($add)->getValueOrExecOnFailure($this->extractErrorCode());

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
        $errorCode = $success->flatMap($fail)->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the error is as expected
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
