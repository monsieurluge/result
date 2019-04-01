<?php

namespace tests\unit\Result;

use Closure;
use monsieurluge\Result\Action\Action;
use monsieurluge\Result\Action\CustomAction;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Result;
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
        $success = new Success('success');

        // WHEN the value is requested
        $value = $success->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN the value the one used to create the result object
        $this->assertSame('success', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::map
     * @covers monsieurluge\Result\Result\Success::getValueOrExecOnFailure
     */
    public function testMapChangeTheResultValue()
    {
        // GIVEN a successful result
        $success = new Success('success');
        // AND a function which takes a text and returns its uppercase version
        $toUppercase = function(string $value) { return strtoupper($value); };

        // WHEN the function is applied to the result
        // AND the value is requested
        $value = $success
            ->map($toUppercase)
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN the value is the uppercase version of the original one
        $this->assertSame('SUCCESS', $value);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::mapOnFailure
     */
    public function testMapOnFailureIsNotTriggered()
    {
        // GIVEN a successful result
        $success = new Success('success');
        // AND a counter
        $counter = 0;

        // WHEN the counter is incremented if the result is a failure
        $success->mapOnFailure(function() use ($counter) { $counter++; });

        // THEN the counter has not been updated
        $this->assertSame(0, $counter);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::map
     * @covers monsieurluge\Result\Result\Success::mapOnFailure
     * @covers monsieurluge\Result\Result\Success::getValueOrExecOnFailure
     */
    public function testMapThenMapOnFailureCombinations()
    {
        // GIVEN a successful result
        $success = new Success('success');
        // AND a function which takes a text and returns its uppercase version
        $toUpperCase = function($value) { return strtoupper($value); };
        // AND a counter
        $counter = 0;

        // WHEN the function is applied to the result
        // AND the counter is incremented if the result is a failure
        // AND the value is requested
        $value = $success
            ->map($toUpperCase)
            ->mapOnFailure(function() use ($counter) { $counter++; })
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN the value is the uppercase version of the original one
        $this->assertSame('SUCCESS', $value);
        // AND the counter has not been updated
        $this->assertSame(0, $counter);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::map
     * @covers monsieurluge\Result\Result\Success::mapOnFailure
     * @covers monsieurluge\Result\Result\Success::getValueOrExecOnFailure
     */
    public function testMapOnFailureThenMapCombinations()
    {
        // GIVEN a succesful result
        $success = new Success('success');
        // AND a function which takes a text and returns its uppercase version
        $toUpperCase = function($value) { return strtoupper($value); };
        // AND a counter
        $counter = 0;

        // WHEN the counter is incremented if the result is a failure
        // AND the function is applied to the result
        // AND the value is requested
        $result = $success
            ->mapOnFailure(function() use ($counter) { $counter++; })
            ->map($toUpperCase)
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN the value is the uppercase version of the original one
        $this->assertSame('SUCCESS', $result);
        // AND the counter has not been updated
        $this->assertSame(0, $counter);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::then
     */
    public function testThenTriggersTheActionAndReturnsNewResult()
    {
        // GIVEN an action which successfully increments a number by 111
        $incrementBy111 = new class() implements Action {
            public function process($target): Result
            {
                return new Success($target + 111);
            }
        };
        // AND a successful result
        $success = new Success(555);

        // WHEN the action is applied to the result
        // AND its value is requested
        $value = $success
            ->then($incrementBy111)
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN the value is the starting one increased by 111
        $this->assertSame(666, $value);
    }

    /**
     * Returns a Closure: f(Error) -> string
     *
     * @return Closure
     */
    private function returnErrorMessage(): Closure
    {
        return function(Error $error) { return $error->message(); };
    }

}
