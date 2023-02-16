<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\Interfaces;

trait LoggerTrait
{
    private array $logLevels = array(
        LoggerInterface::LEVEL_DEBUG => false,
        LoggerInterface::LEVEL_TRIVIAL => false,
        LoggerInterface::LEVEL_INFO => false,
        LoggerInterface::LEVEL_IMPORTANT => false
    );

    public function setLogLevelEnabled(int $level, bool $enable=true) : self
    {
        $this->logLevels[$level] = $enable;
        return $this;
    }

    public function setAllLoggingEnabled(bool $enabled=true) : self
    {
        return $this
            ->setDebugLoggingEnabled($enabled)
            ->setTrivialLoggingEnabled($enabled)
            ->setInfoLoggingEnabled($enabled)
            ->setImportantLoggingEnabled($enabled);
    }

    public function setDebugLoggingEnabled(bool $enable=true) : self
    {
        return $this->setLogLevelEnabled(LoggerInterface::LEVEL_DEBUG, $enable);
    }

    public function setTrivialLoggingEnabled(bool $enable=true) : self
    {
        return $this->setLogLevelEnabled(LoggerInterface::LEVEL_TRIVIAL, $enable);
    }

    public function setInfoLoggingEnabled(bool $enable=true) : self
    {
        return $this->setLogLevelEnabled(LoggerInterface::LEVEL_INFO, $enable);
    }

    public function setImportantLoggingEnabled(bool $enable=true) : self
    {
        return $this->setLogLevelEnabled(LoggerInterface::LEVEL_IMPORTANT, $enable);
    }

    public function logInfo(string $message, ...$params) : void
    {
        $this->log(LoggerInterface::LEVEL_INFO, $message, ...$params);
    }

    public function logTrivial(string $message, ...$params) : void
    {
        $this->log(LoggerInterface::LEVEL_TRIVIAL, $message, ...$params);
    }

    public function logImportant(string $message, ...$params) : void
    {
        $this->log(LoggerInterface::LEVEL_IMPORTANT, $message, ...$params);
    }

    public function logDebug(string $message, ...$params) : void
    {
        $this->log(LoggerInterface::LEVEL_DEBUG, $message, ...$params);
    }

    public function log(int $level, string $message, ...$params) : void
    {
        if($this->logLevels[$level] !== true) {
            return;
        }

        if(empty($params)) {
            echo $message;
        } else {
            echo sprintf($message, ...$params);
        }

        echo PHP_EOL;
    }
}
