<?php

namespace monsieurluge\Result\Result;

use Closure;
use monsieurluge\Result\Result\BaseCombinedValues;
use monsieurluge\Result\Result\Result;

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
     */
    public function getValueOrExecOnFailure(Closure $expression)
    {
        return $this->and()->getValueOrExecOnFailure($expression);
    }

    /**
     * @inheritDoc
     */
    public function map(Closure $mutate): Result
    {
        return $this->and()->map($mutate);
    }

    /**
     * @inheritDoc
     */
    public function mapOnFailure(Closure $mutateError): Result
    {
        return $this->and()->mapOnFailure($mutateError);
    }

    /**
     * @inheritDoc
     */
    public function then(Closure $doSomething): Result
    {
        return $this->and()->then($doSomething);
    }

    /**
     * Returns the combined values or the first Error encountered.
     * @codeCoverageIgnore
     *
     * @return Result either a Result&lt;{x,y}&gt; or an Error
     */
    private function and(): Result
    {
        return $this->firstResult->then($this->combineWith($this->secondResult, $this->combine()));
    }

    /**
     * Returns a function as follows: x -> y -> Result&lt;{x,y}&gt;
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
     * Returns an action which do try to combine a result's value with an other value.
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
