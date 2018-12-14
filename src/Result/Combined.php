<?php

namespace monsieurluge\Result\Result;

use Closure;
use monsieurluge\Result\Action\Action;
use monsieurluge\Result\Result\Result;

final class Combined implements Result
{

        /**
         * @inheritDoc
         */
        public function getValueOrExecOnFailure(Closure $expression)
        {
            return false;
        }

        /**
         * @inheritDoc
         */
        public function map(Closure $expression): Result
        {
            return $this;
        }

        /**
         * @inheritDoc
         */
        public function mapOnFailure(Closure $expression): Result
        {
            return $this;
        }

        /**
         * @inheritDoc
         */
        public function then(Action $action): Result
        {
            return $this;
        }
}
