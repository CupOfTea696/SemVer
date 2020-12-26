<?php

namespace CupOfTea\SemVer;

abstract class BaseVersion extends Versionable
{
    protected bool $prefix;

    /**
     * {@inheritDoc}
     * @throws \CupOfTea\SemVer\InvalidArgumentException
     */
    public function __construct(string $version = 'v0.1.0')
    {
        $version = trim($version);

        if (! $version) {
            throw new InvalidArgumentException('Invalid Semver string');
        }

        $split = explode('.', $version);
        $versions = count($split);

        if ($versions < 3) {
            $version .= $versions === 1 ? '.0.0' : '.0';
        }

        if (! preg_match(static::REGEX, $version, $matches)) {
            throw new InvalidArgumentException('Invalid Semver string');
        }

        $this->handleMatches($matches);
        $this->handlePrefix($matches);
    }

    /**
     * {@inheritDoc}
     */
    public function hasPrefix(): bool
    {
        return $this->prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion(): string
    {
        return ($this->prefix ? 'v' : '') . parent::getVersion();
    }

    /**
     * Use the regex matches to set the version components.
     *
     * @param  array  $matches
     * @return void
     */
    protected function handleMatches(array $matches): void
    {
        $this->major = $matches['major'];
        $this->minor = $matches['minor'];
        $this->patch = $matches['patch'];
        $this->prerelease = $matches['prerelease'] ?? null;
        $this->build = $matches['build'] ?? null;
    }

    /**
     * Use the regex matches to set the correct prefix.
     *
     * @param  array  $matches
     * @return void
     */
    abstract protected function handlePrefix(array $matches): void;
}