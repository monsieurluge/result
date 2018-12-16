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
            ->then($this->needRefactoring1($expression));
    }

    /**
     * @inheritDoc
     */
    public function then(Action $action): Result
    {
        return $this;
    }

    private function needRefactoring1($expression): Action
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
                    ->then($this->needRefactoring2($target));
            }

            private function needRefactoring2($firstValue): Action
            {
                return new class($firstValue) implements Action
                {
                    private $firstValue;

                    public function __construct($firstValue)
                    {
                        $this->firstValue = $firstValue;
                    }

                    public function process($target): Result // target = second value
                    {
                        return new Combined(
                            new Success($this->firstValue),
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
