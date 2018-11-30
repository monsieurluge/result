<?php

namespace tests\unit\Result;

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
        // GIVEN
        $testSubject = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN
        $result = $testSubject->getValueOrExecOnFailure(function(Error $error) { return $error->message(); });

        // THEN
        $this->assertSame('failure', $result);
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::map
     */
    public function testMapDoesNotApply()
    {
        // GIVEN
        $testSubject = new class() {
            private $value = 0;
            public function incrementBy(int $step) { $this->value += $step; }
            public function value() { return $this->value; }
        };

        $incrementBy100 = function() use ($testSubject) { $testSubject->incrementBy(100); };

        $result = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN
        $result->map($incrementBy100);

        // THEN
        $this->assertSame(0, $testSubject->value());
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::mapOnFailure
     */
    public function testMapOnFailureIsCalledWithTheErrorObject()
    {
        // GIVEN
        $testSubject = new class() {
            private $message = '';
            public function updateMessage(string $content) { $this->message = sprintf('[KO] %s', $content); }
            public function message() { return $this->message; }
        };

        $updateErrorMessage = function(Error $error) use ($testSubject) {
            $testSubject->updateMessage($error->message());
            return $error;
        };

        $failure = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN
        $failure->mapOnFailure($updateErrorMessage);

        // THEN
        $this->assertSame('[KO] failure', $testSubject->message());
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
     * @covers monsieurluge\Result\Result\Failure::else
     */
    public function testElseTriggersTheActionOnce()
    {
        // GIVEN
        $testSubject = new class() {
            private $count = 0;
            public function incrementByOne() { $this->count++; }
            public function value() { return $this->count; }
        };

        $incrementCounter = new CustomAction(function() use ($testSubject) { $testSubject->incrementByOne(); });

        $result = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN
        $result->else($incrementCounter);

        // THEN
        $this->assertSame(1, $testSubject->value());
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::else
     * @covers monsieurluge\Result\Result\Failure::then
     */
    public function testThenFollowedByElseTriggersTheElseActionOnce()
    {
        // GIVEN
        $testSubject = new class() {
            private $text = 'failure';
            public function addWord(string $word) { $this->text = sprintf('%s %s', $this->text, $word); }
            public function content() { return $this->text; }
        };

        $addElseWord = new CustomAction(function() use ($testSubject) { $testSubject->addWord('else'); });

        $addThenWord = new CustomAction(function() use ($testSubject) { $testSubject->addWord('then'); });

        $result = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN
        $result->then($addThenWord)->else($addElseWord);

        // THEN
        $this->assertSame('failure else', $testSubject->content());
    }

    /**
     * @covers monsieurluge\Result\Result\Failure::else
     * @covers monsieurluge\Result\Result\Failure::then
     */
    public function testElseFollowedByThenTriggersTheElseActionOnce()
    {
        // GIVEN
        $testSubject = new class() {
            private $text = 'failure';
            public function addWord(string $word) { $this->text = sprintf('%s %s', $this->text, $word); }
            public function content() { return $this->text; }
        };

        $addElseWord = new CustomAction(function() use ($testSubject) { $testSubject->addWord('else'); });

        $addThenWord = new CustomAction(function() use ($testSubject) { $testSubject->addWord('then'); });

        $result = new Failure(new BaseError('err-1234', 'failure'));

        // WHEN
        $result->else($addElseWord)->then($addThenWord);

        // THEN
        $this->assertSame('failure else', $testSubject->content());
    }

}
