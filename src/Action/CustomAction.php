<?php

namespace monsieurluge\Result\Action;

use Closure;
use monsieurluge\Result\Action\Action;

/**
 * A generic action for which the behavior is determined at its construction.
 * @immutable
 */
final class CustomAction implements Action
{

    /** @var Closure **/
    private $expression;

    /**
     * @codeCoverageIgnore
     *
     * @param Closure $expression the expression to apply to the target
     */
    public function __construct(Closure $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @inheritDoc
     */
    public function process($target): void
    {
        ($this->expression)($target);
    }

}
