<?php

namespace CupOfTea\SemVer;

class UnprefixedVersion extends BaseVersion
{
    /**
     * {@inheritDoc}
     */
    final public function hasPrefix(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion(): string
    {
        return Versionable::getVersion();
    }

    /**
     * {@inheritDoc}
     */
    final protected function handlePrefix(array $matches): void
    {
        $this->prefix = true;
    }
}