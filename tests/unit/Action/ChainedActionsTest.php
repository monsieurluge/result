<?php

namespace UnitTests\Action;

use Closure;
use monsieurluge\Result\Action\ChainedActions;
use monsieurluge\Result\Action\CustomAction;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Failure;
use monsieurluge\Result\Result\Result;
use monsieurluge\Result\Result\Success;
use PHPUnit\Framework\TestCase;

final class ChainedActionsTest extends TestCase
{
    /**
     * @covers monsieurluge\Result\Action\ChainedActions::process
     */
    public function testNoChangeWhenThereIsNoAction()
    {
        // GIVEN no actions
        $actions = new ChainedActions();
        // AND a target string
        $target = 'test';

        // WHEN the actions are applied
        $result = $actions->process($target)->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the string is the same
        $this->assertSame('test', $result);
        // AND the target is the same
        $this->assertSame('test', $target);
    }

    /**
     * @covers monsieurluge\Result\Action\ChainedActions::chain
     * @covers monsieurluge\Result\Action\ChainedActions::process
     */
    public function testOneActionDoesOneChange()
    {
        // GIVEN an action which adds a prefix to a string
        $addOk = new CustomAction(function ($text) { return new Success(sprintf('[OK] %s', $text)); });
        // AND the chained action (only one action for now)
        $actions = (new ChainedActions())->chain($addOk);
        // AND a target string
        $target = 'test';

        // WHEN the actions are applied
        $result = $actions->process($target)->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the prefix is added once
        $this->assertSame('[OK] test', $result);
        // AND the target is the same
        $this->assertSame('test', $target);
    }

    /**
     * @covers monsieurluge\Result\Action\ChainedActions::chain
     * @covers monsieurluge\Result\Action\ChainedActions::process
     */
    public function testSameActionChainedTwiceDoesTwoChanges()
    {
        // GIVEN an action which adds a prefix to a string
        $addOk = new CustomAction(function ($text) { return new Success(sprintf('[OK] %s', $text)); });
        // AND the action chained twice
        $actions = (new ChainedActions())->chain($addOk)->chain($addOk);
        // AND a target string
        $target = 'test';

        // WHEN the actions are applied
        $result = $actions->process($target)->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the prefix is added twice
        $this->assertSame('[OK] [OK] test', $result);
        // AND the target is the same
        $this->assertSame('test', $target);
    }

    /**
     * @covers monsieurluge\Result\Action\ChainedActions::chain
     * @covers monsieurluge\Result\Action\ChainedActions::process
     */
    public function testTwoActionsAreAppliedInTheRightOrder()
    {
        // GIVEN an action which adds the "[OK]" prefix to a string
        $addOk = new CustomAction(function ($text) { return new Success(sprintf('[OK] %s', $text)); });
        // GIVEN an action which adds the "!!" prefix to a string
        $addExclamationMarks = new CustomAction(function ($text) { return new Success(sprintf('!! %s', $text)); });
        // AND the chained actions
        $actions = (new ChainedActions())->chain($addExclamationMarks)->chain($addOk);
        // AND a target string
        $target = 'test';

        // WHEN the actions are applied
        $result = $actions->process($target)->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the prefixes are added in the right order (first "!!", then "[OK]" -> "[OK] !!")
        $this->assertSame('[OK] !! test', $result);
        // AND the target is the same
        $this->assertSame('test', $target);
    }

    /**
     * @covers monsieurluge\Result\Action\ChainedActions::chain
     * @covers monsieurluge\Result\Action\ChainedActions::process
     */
    public function testErrorIsReturnedWhenAtLeastOneActionFails()
    {
        // GIVEN an action which adds the "[OK]" prefix to a string
        $addOk = new CustomAction(function ($text) { return new Success(sprintf('[OK] %s', $text)); });
        // AND an action which always returns an Error
        $fail = new CustomAction(function () { return new Failure(new BaseError('err-1', 'foo')); });
        // AND the chained actions
        $actions = (new ChainedActions())->chain($addOk)->chain($fail);
        // AND a target string
        $target = 'test';

        // WHEN the actions are applied
        $result = $actions->process($target)->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN an Error is returned
        $this->assertSame('err-1', $result);
        // AND the target is the same
        $this->assertSame('test', $target);
    }

    /**
     * @covers monsieurluge\Result\Action\ChainedActions::chain
     * @covers monsieurluge\Result\Action\ChainedActions::process
     */
    public function testFirstEncouteredErrorIsReturnedWhenAtLeastOneActionFails()
    {
        // GIVEN an action which adds the "[OK]" prefix to a string
        $addOk = new CustomAction(function ($text) { return new Success(sprintf('[OK] %s', $text)); });
        // AND an action which always returns an Error
        $fail = new CustomAction(function () { return new Failure(new BaseError('err-1', 'foo')); });
        // AND an other action which always returns an Error
        $fail2 = new CustomAction(function () { return new Failure(new BaseError('err-2', 'foo')); });
        // AND the chained actions
        $actions = (new ChainedActions())->chain($addOk)->chain($fail)->chain($addOk)->chain($fail2)->chain($addOk);
        // AND a target string
        $target = 'test';

        // WHEN the actions are applied
        $result = $actions->process($target)->getValueOrExecOnFailure($this->extractErrorCode());

        // THEN the first Error is returned
        $this->assertSame('err-1', $result);
        // AND the target is the same
        $this->assertSame('test', $target);
    }

    /**
     * Returns a Closure as follows: f(Error) -> string
     *   where the string returned is the Error's code.
     *
     * @return Closure
     */
    private function extractErrorCode(): Closure
    {
        return function (Error $error) {
            return $error->code();
        };
    }
}
