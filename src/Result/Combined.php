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
     */
    public function else(Closure $doSomethingWithError): Result
    {
        $this->and()->else($doSomethingWithError);

        return $this;
    }

    /**
     * @inheritDoc
     * @return mixed either CombinedValues or Closure's result
     */
    public function getValueOrExecOnFailure(Closure $expression)
    {
        return $this->and()->getValueOrExecOnFailure($expression);
    }

    /**
     * @inheritDoc
     */
    public function map(Closure $expression): Result
    {
        return $this->and()->map($expression);
    }

    /**
     * @inheritDoc
     */
    public function mapOnFailure(Closure $expression): Result
    {
        return $this->and()->mapOnFailure($expression);
    }

    /**
     * @inheritDoc
     */
    public function thenTemp(Closure $action): Result
    {
        return $this->and()->thenTemp($action);
    }

    /**
     * Returns the combined values or the first Error encountered.
     * @codeCoverageIgnore
     *
     * @return Result either a Result<{x,y}> or an Error
     */
    private function and(): Result
    {
        return $this->firstResult->thenTemp($this->combineWith($this->secondResult, $this->combine()));
    }

    /**
     * Returns a Closure as follows: f(x) -> f(y) -> Result<{x,y}>
     * @codeCoverageIgnore
     *
     * @return Closure
     */
    private function combine(): Closure
    {
        return function ($firstValue) {
            return function ($secondValue) use ($firstValue) {
                return new BaseCombinedValues($firstValue, $secondValue);
            };
        };
    }

    /**
     * Returns an Action which do try to combine a Result's value with an other value.
     * @codeCoverageIgnore
     *
     * @param Result  $result  the Result to combine with
     * @param Closure $combine the "combine" function
     *
     * @return Closure
     */
    private function combineWith(Result $result, Closure $combine): Closure
    {
        return function ($target) use ($result, $combine)
        {
            return $result->map(($combine)($target));
        };
    }

}
