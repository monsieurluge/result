<?php

namespace monsieurluge\Result\ErrorFactory;

use InvalidArgumentException;
use monsieurluge\Result\Error\Error;

final class FileErrorFactory implements ErrorFactory
{
    /** @var string */
    private $path;

    public function __construct(string $configFile)
    {
        $this->checkFile($configFile);

        $this->path = $configFile;
    }

    /**
     * @inheritDoc
     */
    public function create(string $name, array $replacements = []): Error
    {
        $configuration = json_decode(file_get_contents($this->path), true);

        if (is_null($configuration)) {
            throw new InvalidArgumentException(sprintf(
                'cannot read the error\'s configuration file "%s", the json may be incorrect',
                $this->path
            ));
        }

        throw new \RuntimeException(sprintf('method %s::%s not fully implemented', __CLASS__, __FUNCTION__));
    }

    /**
     * Checks if the config file exist.
     *
     * @param string $path
     *
     * @throws InvalidArgumentException if the file doesn't exist
     */
    private function checkFile(string $path): void
    {
        if (false === file_exists($path)) {
            throw new InvalidArgumentException(sprintf(
                'the provided error\'s configuration file "%s" doesn\'t exist',
                $path
            ));
        }
    }
}
