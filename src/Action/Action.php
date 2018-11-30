<?php

namespace monsieurluge\Result\Action;

interface Action
{

    /**
     * Processes the action using the provided target.
     *
     * @param mixed $target the action's target
     */
    public function process($target): void;

}
