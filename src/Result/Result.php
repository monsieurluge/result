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
     * <code>getValueOrExecOnFailure: (Error -> &lt;U&gt;) -> &lt;T&gt;|&lt;U&gt;</code>
     *
     * @param Closure $expression the expression as follows: Error -> &lt;U&gt;
     *
     * @return mixed either the result's value (&lt;T&gt;) or the expression's return value (&lt;U&gt;)
     */
    public function getValueOrExecOnFailure(Closure $expression);

    /**
     * Maps the successful result's value to the mutation function and returns a new success.
     *
     * <code>map: (&lt;T&gt; -> &lt;U&gt;) -> Result&lt;U&gt;</code>
     *
     * @param Closure $mutate the mutation as follows: &lt;T&gt; -> &lt;U&gt;
     *
     * @return Result a Result&lt;U&gt;
     */
    public function map(Closure $mutate): Result;

    /**
     * Maps the failure's error to the mutation function and returns a new failure.
     *
     * <code>mapOnFailure: (Error -> Error) -> Result</code>
     *
     * @param Closure $mutateError the mutation as follows: Error -> Error
     *
     * @return Result a Result&lt;T&gt;
     */
    public function mapOnFailure(Closure $mutateError): Result;

    /**
     * Calls the action on the successful result's value.
     * The action MUST return a Result.
     * The action SHOULD return a Result&lt;T&gt;
     *
     * <code>then: (&lt;T&gt; -> Result&lt;T&gt;) -> Result&lt;T&gt;</code>
     *
     * @param Closure $doSomething the action as follows: &lt;T&gt; -> Result&lt;T&gt;
     *
     * @return Result a Result&lt;T&gt;
     */
    public function then(Closure $doSomething): Result;
}
