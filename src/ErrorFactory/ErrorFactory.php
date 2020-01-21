<?php

namespace monsieurluge\Result\ErrorFactory;

use monsieurluge\Result\Error\Error;

interface ErrorFactory
{
    /**
     * Creates the Error.
     *
     * @param string               $name         the error's name
     * @param array<string,string> $replacements a string replacement list as follows: [ 'name'=>'replacement', 'name 2'=>'replacement 2'... ]
     *
     * @return Error
     */
    public function create(string $name, array $replacements = []): Error;
}
