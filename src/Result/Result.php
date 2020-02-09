<?php

namespace monsieurluge\Result\Result;

use Closure;

/**
 * Result interface.
 * A Result "contains" either the desired value or an Error.
 */
interface Result
{
    /**
     * Act on the failure's error, then returns the failure.
     *
     * @param Closure $doSomethingWithError the action as follows: Error -> void
     *
     * @return Result the same failure
     */
    public function else(Closure $doSomethingWithError): Result;

    /**
     * Calls the action on the successful result's value.
     * The action MUST return a Result.
     * The action SHOULD return a Result<T>
     *
     * <code>flatMap: (<T> -> Result<T>) -> Result<T></code>
     *
     * @param Closure $doSomething the action as follows: <T> -> Result<T>
     *
     * @return Result a Result<T>
     */
    public function flatMap(Closure $doSomething): Result;

    /**
     * Returns the result's value if it succeeded or the expression's return value
     *   if it is a failure. The failure's error will be provided to the expression.
     *
     * <code>getOr: (Error -> <U>) -> <T>|<U></code>
     *
     * @param Closure $expression the expression as follows: Error -> <U>
     *
     * @return mixed either the result's value (<T>) or the expression's return value (<U>)
     */
    public function getOr(Closure $expression);

    /**
     * Combines the current result with the provided one.
     *
     * @param Result $another a Result<U>
     *
     * @return Result a Result<T,U>
     */
    public function join(Result $another): Result;

    /**
     * Maps the successful result's value to the mutation function and returns a new success.
     *
     * <code>map: (<T> -> <U>) -> Result<U></code>
     *
     * @param Closure $mutate the mutation as follows: <T> -> <U>
     *
     * @return Result a Result<U>
     */
    public function map(Closure $mutate): Result;

    /**
     * Calls the action on the successful result's value.
     *
     * <code>then: (<T> -> void) -> Result<T></code>
     *
     * @param Closure $doSomething the action as follows: <T> -> void
     *
     * @return Result a Result<T>
     */
    public function then(Closure $doSomething): Result;
}
