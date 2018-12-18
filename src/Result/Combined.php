<?php

namespace monsieurluge\Result\Result;

use Closure;
use monsieurluge\Result\Action\Action;
use monsieurluge\Result\Result\BaseCombinedValues;
use monsieurluge\Result\Result\Result;
use monsieurluge\Result\Result\Success;

/**
 * A combined results.
 */
final class Combined implements Result
{

    /** @var Result **/
    private $firstResult;
    /** @var Result **/
    private $secondResult;

    /**
     * @codeCoverageIgnore
     *
     * @param Result $first
     * @param Result $second
     */
    public function __construct(Result $first, Result $second)
    {
        $this->firstResult  = $first;
        $this->secondResult = $second;
    }

    /**
     * @inheritDoc
     * @return mixed either CombinedValues or Closure's result
     */
    public function getValueOrExecOnFailure(Closure $expression)
    {
        return $this->firstResult
            ->map(function ($firstValue) use ($expression) {
                return $this->secondResult
                    ->map(function ($secondValue) use ($firstValue) {
                        return new BaseCombinedValues($firstValue, $secondValue);
                    })
                    ->getValueOrExecOnFailure($expression);
            })
            ->getValueOrExecOnFailure($expression);
    }

    /**
     * @inheritDoc
     */
    public function map(Closure $expression): Result
    {
        return $this->firstResult->then($this->callExpressionOnCombinedResults($expression, $this->secondResult));
    }

    /**
     * @inheritDoc
     */
    public function mapOnFailure(Closure $expression): Result
    {
        return $this->firstResult
            ->mapOnFailure($expression)
            ->then($this->createCombineOrMapExpressionAction($expression));
    }

    /**
     * @inheritDoc
     */
    public function then(Action $action): Result
    {
        return $this->firstResult
            ->then($this->processWithFirstValue($action, $this->processWithSecondValue(), $this->secondResult));
    }

    /**
     * [processWithFirstValue description]
     * @codeCoverageIgnore
     *
     * @param  [type] $action       [description]
     * @param  [type] $nextAction   [description]
     * @param  [type] $secondResult [description]
     *
     * @return Action               [description]
     */
    private function processWithFirstValue($action, $nextAction, $secondResult): Action
    {
        return new class($action, $nextAction, $secondResult) implements Action
        {
            private $action;
            private $nextAction;
            private $secondResult;

            public function __construct(Action $action, Closure $nextAction, Result $secondResult)
            {
                $this->action       = $action;
                $this->nextAction   = $nextAction;
                $this->secondResult = $secondResult;
            }

            public function process($target): Result // first value
            {
                return $this->secondResult->then(($this->nextAction)($this->action, $target));
            }
        };
    }

    /**
     * [processWithSecondValue description]
     * @codeCoverageIgnore
     *
     * @return Closure [description]
     */
    private function processWithSecondValue(): Closure
    {
        return function($action, $firstValue): Action
        {
            return new class($action, $firstValue) implements Action
            {
                private $action;
                private $firstValue;

                public function __construct(Action $action, $firstValue)
                {
                    $this->action     = $action;
                    $this->firstValue = $firstValue;
                }

                public function process($target): Result // second value
                {
                    return $this->action->process(new BaseCombinedValues($this->firstValue, $target));
                }
            };
        };
    }

    /**
     * [createCombineOrMapExpressionAction description]
     * @codeCoverageIgnore
     *
     * @param  [type] $expression [description]
     *
     * @return Action             [description]
     */
    private function createCombineOrMapExpressionAction($expression): Action
    {
        return new class($expression, $this->secondResult) implements Action
        {
            private $expression;
            private $secondResult;

            public function __construct(Closure $expression, Result $secondResult)
            {
                $this->expression   = $expression;
                $this->secondResult = $secondResult;
            }

            public function process($target): Result // target = first value
            {
                return $this->secondResult
                    ->mapOnFailure($this->expression)
                    ->then($this->createCombineTwoValuesAction($target));
            }

            private function createCombineTwoValuesAction($first): Action
            {
                return new class($first) implements Action
                {
                    private $first;

                    public function __construct($first)
                    {
                        $this->first = $first;
                    }

                    public function process($target): Result // target = second value
                    {
                        return new Combined(
                            new Success($this->first),
                            new Success($target)
                        );
                    }
                };
            }
        };
    }

    /**
     * Returns an Action which calls the expression on the target (the first Result's value)
     *   and the second Result's value, all combined.
     * @codeCoverageIgnore
     *
     * @param Closure $expression   the expression to call as follows: f(CombinedValues) -> mixed
     * @param Result  $secondResult the secondary Result from wich the value is mapped
     *
     * @return Action
     */
    private function callExpressionOnCombinedResults(Closure $expression, Result $secondResult): Action
    {
        return new class($expression, $secondResult) implements Action
        {
            private $expression;
            private $secondResult;

            public function __construct(Closure $expression, Result $secondResult)
            {
                $this->expression   = $expression;
                $this->secondResult = $secondResult;
            }

            public function process($target): Result
            {
                return $this->secondResult->map($this->callTheExpressionUsingTheValues($this->expression, $target));
            }

            private function callTheExpressionUsingTheValues(Closure $expression, $firstValue): Closure
            {
                return function ($secondValue) use ($expression, $firstValue) {
                    return ($expression)(new BaseCombinedValues($firstValue, $secondValue));
                };
            }
        };
    }

}
