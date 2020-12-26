<?php

namespace CupOfTea\SemVer;

use CupOfTea\SemVer\Contracts\Version as VersionContract;

/**
 * @property-read int $major
 * @property-read int $minor
 * @property-read int $patch
 * @property-read string|null $prerelease
 * @property-read string|null $build
 *
 * @method bool eq(\CupOfTea\SemVer\Contracts\Version|string $compare)    Check if the Version is equal to the given version.
 * @method bool gt(\CupOfTea\SemVer\Contracts\Version|string $compare)    Check if the Version is greater than the given version.
 * @method bool gte(\CupOfTea\SemVer\Contracts\Version|string $compare)   Check if the Version is greater than or equal to the given version.
 * @method bool lt(\CupOfTea\SemVer\Contracts\Version|string $compare)    Check if the Version is less than the given version.
 * @method bool lte(\CupOfTea\SemVer\Contracts\Version|string $compare)   Check if the Version is less than or equal to the given version.
 */
abstract class Versionable implements VersionContract
{
    protected const READONLY = ['major', 'minor', 'patch', 'prerelease', 'build'];
    protected const COMP_METHODS = ['eq', 'gt', 'gte', 'lt', 'lte'];
    protected const REGEX = '/^(?:(?P<prefix>v\s*))?(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<build>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

    protected int $major;
    protected int $minor;
    protected int $patch;
    protected ?string $prerelease;
    protected ?string $build;

    /**
     * Create a new Version instance from the given components.
     *
     * @param  int  $major
     * @param  int  $minor
     * @param  int  $patch
     * @param  string|null  $prerelease
     * @param  string|null  $build
     * @return static
     */
    public static function create(int $major = 0, int $minor = 0, int $patch = 0, ?string $prerelease = null, ?string $build = null): Versionable
    {
        $version = new static();
        $version->setMajor($major);
        $version->setMinor($minor);
        $version->setPatch($patch);
        $version->setPrerelease($prerelease);
        $version->setBuild($build);

        return $version;
    }

    /**
     * {@inheritDoc}
     */
    public function getMajor(): int
    {
        return $this->major;
    }

    /**
     * {@inheritDoc}
     */
    public function setMajor(int $major): void
    {
        $this->major = $major;
    }

    /**
     * {@inheritDoc}
     */
    public function getMinor(): int
    {
        return $this->minor;
    }

    /**
     * {@inheritDoc}
     */
    public function setMinor(int $minor): void
    {
        $this->minor = $minor;
    }

    /**
     * {@inheritDoc}
     */
    public function getPatch(): int
    {
        return $this->patch;
    }

    /**
     * {@inheritDoc}
     */
    public function setPatch(int $patch): void
    {
        $this->patch = $patch;
    }

    /**
     * {@inheritDoc}
     */
    public function isPrerelease(): bool
    {
        return isset($this->prerelease);
    }

    /**
     * {@inheritDoc}
     */
    public function getPrerelease(): ?string
    {
        return $this->prerelease;
    }

    /**
     * {@inheritDoc}
     */
    public function setPrerelease(?string $prerelease): void
    {
        $this->prerelease = $prerelease;
    }

    /**
     * {@inheritDoc}
     */
    public function unsetPrerelease(): void
    {
        $this->prerelease = null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasBuild(): bool
    {
        return isset($this->build);
    }

    /**
     * {@inheritDoc}
     */
    public function getBuild(): ?string
    {
        return $this->build;
    }

    /**
     * {@inheritDoc}
     */
    public function setBuild(?string $build): void
    {
        $this->build = $build;
    }

    /**
     * {@inheritDoc}
     */
    public function unsetBuild(): void
    {
        $this->build = null;
    }

    /**
     * Bump the patch major component.
     *
     * @param  int  $by
     * @return $this
     */
    public function bumpMajor(int $by = 1): Versionable
    {
        $this->validateIncrementValue($by);

        $this->major += $by;
        $this->setMinor(0);
        $this->setPatch(0);
        $this->release();

        return $this;
    }

    /**
     * Bump the minor version component.
     *
     * @param  int  $by
     * @return $this
     */
    public function bumpMinor(int $by = 1): Versionable
    {
        $this->validateIncrementValue($by);

        $this->minor += $by;
        $this->setPatch(0);
        $this->release();

        return $this;
    }

    /**
     * Bump the patch version component.
     *
     * @param  int  $by
     * @return $this
     */
    public function bumpPatch(int $by = 1): Versionable
    {
        $this->validateIncrementValue($by);

        $this->patch += $by;
        $this->release();

        return $this;
    }

    /**
     * Bump the version to a release state.
     *
     * @return $this
     */
    public function release(): Versionable
    {
        $this->unsetBuild();
        $this->unsetPrerelease();

        return $this;
    }

    /**
     * Get the version string.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return sprintf('%s.%s.%s', $this->major, $this->minor, $this->patch)
            . ($this->prerelease ? '-' . $this->prerelease : '')
            . ($this->build ? '+' . $this->build : '');
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->getVersion();
    }

    /**
     * Returns the string representation of the Version.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getVersion();
    }

    /**
     * Dynamically pass comparison methods to Compare.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \CupOfTea\SemVer\BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        if (in_array($method, static::COMP_METHODS, true)) {
            return Compare::{$method}($this, ...$parameters);
        }

        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }

    /**
     * Get a property by name.
     *
     * @param  string  $name
     * @return mixed
     *
     * @throws \CupOfTea\SemVer\PropertyNotFoundException
     */
    public function __get(string $name)
    {
        if (in_array($name, self::READONLY, true)) {
            return $this->{$name};
        }

        throw new PropertyNotFoundException(sprintf('The property %s does not exist', $name));
    }

    /**
     * Set a property by name.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return void
     *
     * @throws \CupOfTea\SemVer\PropertyReadOnlyException
     * @throws \CupOfTea\SemVer\PropertyNotFoundException
     */
    public function __set(string $name, $value): void
    {
        if (in_array($name, self::READONLY, true)) {
            throw new PropertyReadOnlyException(sprintf('The property %s is read-only', $name));
        }

        throw new PropertyNotFoundException(sprintf('The property %s does not exist', $name));
    }

    /**
     * Check if a property is set by name.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws \CupOfTea\SemVer\PropertyNotFoundException
     */
    public function __isset(string $name): bool
    {
        if (in_array($name, self::READONLY, true)) {
            return isset($this->{$name});
        }

        throw new PropertyNotFoundException(sprintf('The property %s does not exist', $name));
    }

    /**
     * Unset a property by name.
     *
     * @param  string  $name
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->__set($name, null);
    }

    /**
     * Validate if the given value can be used to increment a version component.
     *
     * @param  int  $by
     * @return void
     *
     * @throws \CupOfTea\SemVer\InvalidArgumentException
     */
    protected function validateIncrementValue(int $by): void
    {
        if ($by < 1) {
            throw new InvalidArgumentException('You must increment by at least 1');
        }
    }
}