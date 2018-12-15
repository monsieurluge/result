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
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mapOnFailure(Closure $expression): Result
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function then(Action $action): Result
    {
        return $this;
    }

}
