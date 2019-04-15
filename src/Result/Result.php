<?php

namespace monsieurluge\Result\Result;

use Closure;
use monsieurluge\Result\Action\Action;

interface Result
{

    /**
     * Returns the result's value if it succeeded or the expression's return value
     *   if it is a failure. The failure's error will be provided to the expression.
     *
     * <code>getValueOrExecOnFailure(f(Error):&lt;U&gt;):&lt;T&gt;|&lt;U&gt;</code>
     *
     * @param Closure $expression the expression as follows: f(Error):&lt;U&gt;
     *
     * @return mixed
     */
    public function getValueOrExecOnFailure(Closure $expression);

    /**
     * Maps the &lt;T&gt; result's value to the expression and returns a Result&lt;U&gt;
     *   where &lt;U&gt; is the expression's return value.
     *
     * <code>map(f(&lt;T&gt;):&lt;U&gt;):Result&lt;U&gt;</code>
     *
     * @param Closure $expression a funciton as follows: f(&lt;T&gt;):&lt;U&gt;
     *
     * @return Result a Result&lt;U&gt;
     */
    public function map(Closure $expression): Result;

    /**
     * Maps the failure's error to the expression and returns a Result&lt;U&gt;
     *   where &lt;U&gt; is the expression's return value.
     *
     * <code>mapOnFailure(f(Error):&lt;U&gt;):Result&lt;U&gt;</code>
     *
     * @param Closure $expression a function as follows: f(Error):&lt;U&gt;
     *
     * @return Result a Result&lt;U&gt;
     */
    public function mapOnFailure(Closure $expression): Result;

    /**
     * Calls the action on the successful result's value.
     *
     * <code>then(Action):Result&lt;T&gt;</code>
     *
     * @param Action $action the action to call (Action::process(&lt;T&gt;))
     *
     * @return Result a Result&lt;T&gt;
     */
    public function then(Action $action): Result;

}
