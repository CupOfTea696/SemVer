<?php

namespace CupOfTea\SemVer;

class PrefixedVersion extends BaseVersion
{
    /**
     * {@inheritDoc}
     */
    final public function hasPrefix(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion(): string
    {
        return 'v' . Versionable::getVersion();
    }

    /**
     * {@inheritDoc}
     */
    final protected function handlePrefix(array $matches): void
    {
        $this->prefix = true;
    }
}