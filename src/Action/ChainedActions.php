<?php

namespace monsieurluge\Result\Action;

use monsieurluge\Result\Action\Action;
use monsieurluge\Result\Result\Result;
use monsieurluge\Result\Result\Success;

final class ChainedActions implements Action
{
    /** @var Action[] **/
    private $actions;

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->actions = [];
    }

    /**
     * Chains an action.
     *
     * @param Action $action
     *
     * @return Action
     */
    public function chain(Action $action): Action
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function process($target): Result
    {
        return array_reduce(
            $this->actions,
            function (Result $target, Action $action) { return $target->then($action); },
            new Success($target)
        );
    }
}
