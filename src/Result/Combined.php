<?php

namespace monsieurluge\Result\Result;

use Closure;
use monsieurluge\Result\Result\Result;

/**
 * A combined results.
 */
final class Combined implements Result
{
    /** @var Result[] **/
    private $results;

    /**
     * @param Result[] $results
     */
    public function __construct(array $results)
    {
        $this->results = $results;
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
    public function flatMap(Closure $doSomething): Result
    {
        return $this->and()->flatMap($doSomething);
    }

    /**
     * @inheritDoc
     */
    public function getOr(Closure $expression)
    {
        return $this->and()->getOr($expression);
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
    public function then(Closure $doSomething): Result
    {
        return $this->and()->then($doSomething);
    }

    /**
     * Returns the combined values or the first Error encountered.
     *
     * @return Result either a Result<{x,y,z...}> or an Error
     */
    private function and(): Result
    {
        return array_reduce($this->results, $this->combineSuccesses(), new Success([]));
    }

    /**
     * Returns a function which combines successful result values.
     *
     * @return Closure the function as follows: (Result<[x,y...]>, Result<v>) -> Result<[x,y...,v]>
     */
    private function combineSuccesses(): Closure
    {
        return function (Result $carry, Result $item): Result {
            return $carry->then($this->mergeValues($item));
        };
    }

    /**
     * Returns a function which merges a successful result value with an array.
     *
     * @param Result $item the value to merge
     *
     * @return Closure the function as follows: [x,y...] -> Result<[x,y...,v]> where v is the value to merge with
     */
    private function mergeValues(Result $item): Closure
    {
        return function (array $values) use ($item): Result {
            return $item->map(function ($currentValue) use ($values) {
                return array_merge($values, [ $currentValue ]);
            });
        };
    }
}
