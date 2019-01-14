<?php

namespace monsieurluge\Result\Action;

use monsieurluge\Result\Result\Result;

interface Action
{

    /**
     * Processes the action using the provided target and returns a new Result.
     *
     * @param mixed $target the action's target
     *
     * @returns Result
     */
    public function process($target): Result;

}
