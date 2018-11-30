<?php

namespace tests\unit\Result;

use monsieurluge\Result\Action\CustomAction;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
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
        $testSubject = $success->getValueOrExecOnFailure(function(Error $error) { return $error->message(); });

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
            ->getValueOrExecOnFailure(function(Error $error) { return $error->message(); });

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
            ->getValueOrExecOnFailure(function(Error $error) { return $error->message(); });

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
            ->getValueOrExecOnFailure(function(Error $error) { return $error->message(); });

        // THEN
        $this->assertSame('SUCCESS', $result);

        $this->assertSame(0, $testSubject);
    }

    /**
     * @covers monsieurluge\Result\Result\Success::then
     */
    public function testThenTriggersTheAction()
    {
        // GIVEN
        $testSubject = new class() {
            private $value = 600;
            public function incrementBy(int $step) { $this->value += $step; }
            public function value() { return $this->value; }
        };

        $incrementBy66 = new CustomAction(function($target) { $target->incrementBy(66); });

        $success = new Success($testSubject);

        // WHEN
        $success->then($incrementBy66);

        // THEN
        $this->assertSame(666, $testSubject->value());
    }

    /**
     * @covers monsieurluge\Result\Result\Success::else
     */
    public function testElseDoesNotTriggerTheAction()
    {
        // GIVEN
        $testSubject = new class() {
            private $count = 0;
            public function increment() { $this->count++; }
            public function value() { return $this->count; }
        };

        $incrementCounter = new CustomAction(function($target) { $target->increment(); });

        $success = new Success('success');

        // WHEN
        $success->else($incrementCounter);

        // THEN
        $this->assertSame(0, $testSubject->value());
    }

    /**
     * @covers monsieurluge\Result\Result\Success::else
     * @covers monsieurluge\Result\Result\Success::then
     */
    public function testThenFollowedByElseTriggersTheThenAction()
    {
        // GIVEN
        $testSubject = new class() {
            private $text = 'success';
            public function addWord(string $word) { $this->text = sprintf('%s %s', $this->text, $word); }
            public function content() { return $this->text; }
        };

        $addElseWord = new CustomAction(function($target) { $target->addWord('else'); });

        $addThenWord = new CustomAction(function($target) { $target->addWord('then'); });

        $success = new Success($testSubject);

        // WHEN
        $success->then($addThenWord)->else($addElseWord);

        // THEN
        $this->assertSame('success then', $testSubject->content());
    }

    /**
     * @covers monsieurluge\Result\Result\Success::else
     * @covers monsieurluge\Result\Result\Success::then
     */
    public function testElseFollowedByThenTriggersTheThenAction()
    {
        // GIVEN
        $testSubject = new class() {
            private $text = 'success';
            public function addWord(string $word) { $this->text = sprintf('%s %s', $this->text, $word); }
            public function content() { return $this->text; }
        };

        $addElseWord = new CustomAction(function($target) { $target->addWord('else'); });

        $addThenWord = new CustomAction(function($target) { $target->addWord('then'); });

        $success = new Success($testSubject);

        // WHEN
        $success->else($addElseWord)->then($addThenWord);

        // THEN
        $this->assertSame('success then', $testSubject->content());
    }

}
