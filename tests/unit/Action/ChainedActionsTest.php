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
