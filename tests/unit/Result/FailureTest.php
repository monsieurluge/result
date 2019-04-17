<?php

namespace tests\unit\Result;

use Closure;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Action\CustomAction;
use monsieurluge\Result\Result\Failure;
use monsieurluge\Result\Result\Success;
use PHPUnit\Framework\TestCase;

final class FailureTest extends TestCase
{

    /**
     * @covers monsieurluge\Result\Result\Failure::getValueOrExecOnFailure
     */
    public function testTheFailureErrorIsFetched()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN the error's code is fetched
        $code = $failure->getValueOrExecOnFailure(function(Error $error) { return $error->message(); });

        // THEN the error's code is as expected
        $this->assertSame('failure', $code);
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::map
     */
    public function testMapDoesNotApply()
    {
        // GIVEN a failed result
        $result = new Failure(new BaseError('err-1234', 'failure'));
        // AND an object who responds to the "incrementBy" and "value" messages
        $incrementable = new class () {
            public $value = 0;
            public function incrementBy(int $step) { $this->value += $step; }
        };
        // AND a function which helps to increment the "incrementable" object's value
        $incrementBy100 = function() use ($incrementable) { $incrementable->incrementBy(100); };

        // WHEN the "map" message is sent
        $result->map($incrementBy100);

        // THEN the "incrementable" object's value has not been altered
        $this->assertSame(0, $incrementable->value);
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::mapOnFailure
     */
    public function testMapOnFailureIsCalledWithTheErrorObject()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('err-1234', 'failure'));
        // AND a function which prepends " KO" to an error's code
        $prependKoToErrorCode = function(Error $error) { return new BaseError($error->code() . ' KO', $error->message()); };

        // WHEN the "mapOnFailure" message is sent, and the error's code is extracted
        $code = $failure
            ->mapOnFailure($prependKoToErrorCode)
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the error's code is as expected
        $this->assertSame('err-1234 KO', $code);
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::map
     * @covers monsieurluge\Result\Result\Failure::mapOnFailure
     */
    public function testMapFollowedByMapOnFailureCombinations()
    {
        // GIVEN
        $testSubject = new class() {
            private $message = '---';
            private $count = 0;
            public function updateMessage(Error $error) {
                $this->message = sprintf('%s #%s', $error->message(), $error->code());
            }
            public function incrementByOne() { $this->count++; }
            public function message() { return sprintf('[KO] %s, count = %s', $this->message, $this->count); }
        };

        $incrementCounter = function() use ($testSubject) { $testSubject->incrementByOne(); };

        $updateMessageUsingErrorMessage = function(Error $error) use ($testSubject) {
            $testSubject->updateMessage($error);
            return $error;
        };

        $failure = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN
        $failure
            ->map($incrementCounter)
            ->mapOnFailure($updateMessageUsingErrorMessage);

        // THEN
        $this->assertSame('[KO] failure #err-1234, count = 0', $testSubject->message());
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::map
     * @covers monsieurluge\Result\Result\Failure::mapOnFailure
     */
    public function testMapOnFailureFollowedByMapCombinations()
    {
        // GIVEN
        $testSubject = new class() {
            private $message = '---';
            private $count = 0;
            public function updateMessage(string $newMessage) { $this->message = $newMessage; }
            public function incrementByOne() { $this->count++; }
            public function message() { return sprintf('[KO] %s, count = %s', $this->message, $this->count); }
        };

        $incrementCounter = function() use ($testSubject) { $testSubject->incrementByOne(); };

        $updateMessageUsingErrorMessage = function(Error $error) use ($testSubject) {
            $testSubject->updateMessage($error->message());
            return $error;
        };

        $failure = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN
        $failure
            ->mapOnFailure($updateMessageUsingErrorMessage)
            ->map($incrementCounter);

        // THEN
        $this->assertSame('[KO] failure, count = 0', $testSubject->message());
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::then
     */
    public function testThenDoesNotTriggerTheAction()
    {
        // GIVEN
        $testSubject = new class() {
            private $count = 0;
            public function incrementByOne() { $this->count++; }
            public function value() { return $this->count; }
        };

        $incrementCounter = new CustomAction(function($target) { $target->incrementByOne(); });

        $result = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN
        $result->then($incrementCounter);

        // THEN
        $this->assertSame(0, $testSubject->value());
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::thenTemp
     */
    public function testFailureDoesNotTriggerTheThenTempAction()
    {
        // GIVEN a failed result
        $failure = new Failure(new BaseError('err-1234', 'failure'));
        // AND a "counter" object
        $counter = $this->createCounter();

        // WHEN an action is provided
        $failure->thenTemp(function () use ($counter) { $counter->increment(); return new Success('foo'); });

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
        // AND a class which is used to store a text
        $storage = new class () {
            public $text = 'nothing';
            public function store (string $text) { $this->text = $text; }
        };

        // WHEN the else message is sent, and the error's code is fetched
        $code = $failure
            ->else(function (Error $error) use ($storage) { $storage->store($error->code()); })
            ->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the error's code has not been altered
        $this->assertSame('err-1234', $code);
        // AND the stored text is as expected
        $this->assertSame('err-1234', $storage->text);
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

}
