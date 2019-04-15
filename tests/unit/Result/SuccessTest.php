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
        // GIVEN
        $success = new Success('success');

        // WHEN
        $testSubject = $success->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('success', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::map
     * @covers monsieurluge\Result\Result\Success::getValueOrExecOnFailure
     */
    public function testMapChangeTheResultValue()
    {
        // GIVEN
        $success = new Success('success');

        $toUppercase = function(string $value) { return strtoupper($value); };

        // WHEN
        $testSubject = $success
            ->map($toUppercase)
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('SUCCESS', $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::mapOnFailure
     */
    public function testMapOnFailureIsNotTriggered()
    {
        // GIVEN
        $success = new Success('success');

        $testSubject = 0;

        // WHEN
        $success->mapOnFailure(function() use ($testSubject) { $testSubject++; });

        // THEN
        $this->assertSame(0, $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::map
     * @covers monsieurluge\Result\Result\Success::mapOnFailure
     * @covers monsieurluge\Result\Result\Success::getValueOrExecOnFailure
     */
    public function testMapThenMapOnFailureCombinations()
    {
        // GIVEN
        $success = new Success('success');

        $toUpperCase = function($value) { return strtoupper($value); };

        $testSubject = 0;

        // WHEN
        $result = $success
            ->map($toUpperCase)
            ->mapOnFailure(function() use ($testSubject) { $testSubject++; })
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('SUCCESS', $result);

        $this->assertSame(0, $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::map
     * @covers monsieurluge\Result\Result\Success::mapOnFailure
     * @covers monsieurluge\Result\Result\Success::getValueOrExecOnFailure
     */
    public function testMapOnFailureThenMapCombinations()
    {
        // GIVEN
        $success = new Success('success');

        $toUpperCase = function($value) { return strtoupper($value); };

        $testSubject = 0;

        // WHEN
        $result = $success
            ->mapOnFailure(function() use ($testSubject) { $testSubject++; })
            ->map($toUpperCase)
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame('SUCCESS', $result);

        $this->assertSame(0, $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::then
     */
    public function testThenTriggersTheActionAndReturnsNewResult()
    {
        // GIVEN
        $incrementBy111 = new class() implements Action {
            public function process($target): Result
            {
                return new Success($target + 111);
            }
        };

        $success = new Success(555);

        // WHEN
        $testSubject = $success
            ->then($incrementBy111)
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN
        $this->assertSame(666, $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::else
     */
    public function testElseOnSuccessDoesNothing()
    {
        // GIVEN the successful result
        $success = new Success(1234);
        // AND a class which is used to store a text
        $storage = new class () {
            public $text = 'nothing';
            public function store (string $text) { $this->text = $text; }
        };

        // WHEN the else message is sent, and the value is fetched
        $result = $success
            ->else(function (Error $error) use ($storage) { $storage->store($error->code()); })
            ->getValueOrExecOnFailure($this->returnErrorMessage());

        // THEN the result's content has not been altered
        $this->assertSame(1234, $result);
        // AND the storage's text has not been altered
        $this->assertSame('nothing', $storage->text);
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

    /**
     * Returns a Closure: f(Error) -> Action
     *
     * @param string $newMessage
     *
     * @return Action an action which is always a failure
     */
    private function replaceErrorMessageWith(string $newMessage): Action
    {
        return new CustomAction(function (Error $error) use ($newMessage) {
            return new BaseError(
                $error->code(),
                $newMessage
            );
        });
    }

}
