<?php

namespace CupOfTea\SemVer;

use Closure;
use Countable;
use ArrayAccess;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;
use CupOfTea\SemVer\Contracts\ConvertableVersion;
use CupOfTea\SemVer\Contracts\VersionWithMutablePrefix;
use CupOfTea\SemVer\Contracts\Version as VersionContract;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * The underlying versions array.
     *
     * @var \CupOfTea\SemVer\Contracts\Version[]
     */
    protected array $versions;

    protected bool $usePrefix = false;
    protected bool $dontUsePrefix = false;

    /**
     * Create a new Collection instance.
     *
     * @param  array  $versions
     * @return void
     */
    public function __construct(array $versions)
    {
        try {
            $this->versions = array_map(function ($version) {
                return $this->cast($version);
            }, $versions);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('One or more of the given values are not a valid Semver string');
        }
    }

    /**
     * Get all of the versions in the collection.
     *
     * @return \CupOfTea\SemVer\Contracts\Version[]
     */
    public function all(): array
    {
        return $this->versions;
    }

    /**
     * Enable the 'v' prefix on all Versions in the Collection.
     *
     * @return $this
     */
    public function withPrefix(): Collection
    {
        $this->usePrefix = true;
        $this->dontUsePrefix = false;

        $this->versions = $this->normalizePrefix($this->versions);

        return $this;
    }

    /**
     * Disable the 'v' prefix on all Versions in the Collection.
     *
     * @return $this
     */
    public function withoutPrefix(): Collection
    {
        $this->usePrefix = false;
        $this->dontUsePrefix = true;

        $this->versions = $this->normalizePrefix($this->versions);

        return $this;
    }

    /**
     * Remove a version from the collection by key.
     *
     * @param  string|array  $keys
     * @return $this
     */
    public function forget($keys): Collection
    {
        foreach ((array) $keys as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * Get an item from the collection by key.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return \CupOfTea\SemVer\Contracts\Version|null
     */
    public function get($key, $default = null): ?VersionContract
    {
        if (array_key_exists($key, $this->versions)) {
            return $this->versions[$key];
        }

        return $this->cast($default);
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function filter(callable $callback = null): Collection
    {
        if ($callback) {
            return $this->newFromThis(array_filter($this->versions, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return $this->newFromThis(array_filter($this->versions));
    }

    /**
     * Return the first Version in the Collection passing a given truth test.
     *
     * @param  callable|null  $callback
     * @param  \CupOfTea\SemVer\Contracts\Version|string|null  $default
     * @return \CupOfTea\SemVer\Contracts\Version|null
     */
    public function first(callable $callback = null, $default = null): ?VersionContract
    {
        if (is_null($callback)) {
            if (empty($this->versions)) {
                return $this->cast($default);
            }

            /** @noinspection LoopWhichDoesNotLoopInspection */
            foreach ($this->versions as $version) {
                return $version;
            }
        }

        foreach ($this->versions as $key => $version) {
            if ($callback($version, $key)) {
                return $version;
            }
        }

        return $this->cast($default);
    }

    /**
     * Return the last Version in the Collection passing a given truth test.
     *
     * @param  callable|null  $callback
     * @param  \CupOfTea\SemVer\Contracts\Version|string|null  $default
     * @return \CupOfTea\SemVer\Contracts\Version|null
     */
    public function last(callable $callback = null, $default = null): ?VersionContract
    {
        if (is_null($callback)) {
            $versions = $this->versions;

            return empty($versions) ? $this->cast($default) : end($versions);
        }

        return $this->reverse()->first($callback, $default);
    }

    /**
     * Merge the Collection with the given Versions.
     *
     * @param  \CupOfTea\SemVer\Collection|\CupOfTea\SemVer\Contracts\Version[]|string[]  $versions
     * @return static
     */
    public function merge($versions): Collection
    {
        if ($versions instanceof self) {
            $versions = $versions->all();
        } else {
            if (! is_array($versions)) {
                throw new InvalidArgumentException('The versions must be an array or an instance of \CupOfTea\SemVer\Collection');
            }

            $versions = array_map(function ($version) {
                try {
                    return $this->cast($version);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException('The versions array must only contain valid version strings or instances of \CupOfTea\SemVer\Contracts\Version');
                }
            }, $versions);
        }

        return new static(array_merge($this->versions, $versions));
    }

    /**
     * Union the collection with the given items.
     *
     * @param  \CupOfTea\SemVer\Collection|\CupOfTea\SemVer\Contracts\Version[]|string[]  $versions
     * @return static
     */
    public function union($versions): Collection
    {
        if ($versions instanceof self) {
            $versions = $versions->all();
        } else {
            if (! is_array($versions)) {
                throw new InvalidArgumentException('The versions must be an array or an instance of \CupOfTea\SemVer\Collection');
            }

            $versions = array_map(function ($version) {
                try {
                    return $this->cast($version);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException('The versions array must only contain valid version strings or instances of \CupOfTea\SemVer\Contracts\Version');
                }
            }, $versions);
        }

        /** @noinspection AdditionOperationOnArraysInspection */
        return new static($this->versions + $versions);
    }

    /**
     * Get and remove the last version from the collection.
     *
     * @return \CupOfTea\SemVer\Contracts\Version|null
     */
    public function pop(): ?VersionContract
    {
        return array_pop($this->versions);
    }

    /**
     * Push one or more versions onto the end of the collection.
     *
     * @param  mixed  $versions [optional]
     * @return $this
     */
    public function push(...$versions): Collection
    {
        foreach ($versions as $version) {
            $this->versions[] = $this->cast($version);
        }

        return $this;
    }

    /**
     * Push all of the given items onto the collection.
     *
     * @param  iterable  $versions
     * @return static
     */
    public function concat(iterable $versions): Collection
    {
        $result = $this->newFromThis($this->versions);

        foreach ($versions as $version) {
            $result->push($version);
        }

        return $result;
    }

    /**
     * Put a version in the collection by key.
     *
     * @param  mixed  $key
     * @param  mixed  $version
     * @return $this
     */
    public function put($key, $version): Collection
    {
        $this->offsetSet($key, $version);

        return $this;
    }

    /**
     * Reverse versions order.
     *
     * @return static
     */
    public function reverse(): Collection
    {
        return $this->newFromThis(array_reverse($this->versions, true));
    }

    /**
     * Search the collection for a given version and return the corresponding key if successful.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version|string  $value
     * @return mixed|false
     */
    public function search($value)
    {
        if (! $this->useAsCallable($value)) {
            $value = ($value instanceof VersionContract ? $this->normalizePrefix($value) : $this->cast($value))->getVersion();

            foreach ($this->versions as $key => $version) {
                if ($value === $version->getVersion()) {
                    return $key;
                }
            }
        }

        foreach ($this->versions as $key => $version) {
            if ($value($version, $key)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Get and remove the first version from the collection.
     *
     * @return \CupOfTea\SemVer\Contracts\Version|null
     */
    public function shift(): ?VersionContract
    {
        return array_shift($this->versions);
    }

    /**
     * Slice the underlying collection array.
     *
     * @param  int  $offset
     * @param  int|null  $length
     * @return static
     */
    public function slice(int $offset, $length = null): Collection
    {
        return $this->newFromThis(array_slice($this->versions, $offset, $length, true));
    }

    /**
     * Sort versions in ascending order.
     *
     * @return static
     */
    public function sort(): Collection
    {
        $versions = $this->versions;

        uasort($versions, function (VersionContract $v1, VersionContract $v2) {
            return Compare::comp($v1, $v2);
        });

        return $this->newFromThis($versions);
    }

    /**
     * Sort versions in descending order.
     *
     * @return static
     */
    public function sortDesc(): Collection
    {
        $versions = $this->versions;

        uasort($versions, function (VersionContract $v1, VersionContract $v2) {
            return Compare::comp($v2, $v1);
        });

        return $this->newFromThis($versions);
    }

    /**
     * Splice a portion of the underlying collection array.
     *
     * @param  int  $offset
     * @param  int|null  $length
     * @param  \CupOfTea\SemVer\Collection|\CupOfTea\SemVer\Contracts\Version[]|string[]  $replacement
     * @return static
     */
    public function splice(int $offset, ?int $length = null, $replacement = []): Collection
    {
        if (func_num_args() === 1) {
            return $this->newFromThis(array_splice($this->versions, $offset));
        }

        if ($replacement instanceof self) {
            $replacement = $replacement->all();
        } else {
            if (! is_array($replacement)) {
                throw new InvalidArgumentException('The replacement must be an array or an instance of \CupOfTea\SemVer\Collection');
            }

            $replacement = array_map(function ($version) {
                try {
                    return $this->cast($version);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException('The replacement array must only contain valid version strings or instances of \CupOfTea\SemVer\Contracts\Version');
                }
            }, $replacement);
        }

        return $this->newFromThis(array_splice($this->versions, $offset, $length, $replacement));
    }

    /**
     * Get the min version in the collection.
     *
     * @return \CupOfTea\SemVer\Contracts\Version|null
     */
    public function min(): ?VersionContract
    {
        return $this->sort()->first();
    }

    /**
     * Get the max version in the collection.
     *
     * @return \CupOfTea\SemVer\Contracts\Version|null
     */
    public function max(): ?VersionContract
    {
        return $this->sort()->last();
    }

    /**
     * Reset the keys on the underlying array.
     *
     * @return static
     */
    public function values(): Collection
    {
        return $this->newFromThis(array_values($this->versions));
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->versions);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->versions);
    }

    /**
     * Add an item to the collection.
     *
     * @param  mixed  $item
     * @return $this
     */
    public function add($item): Collection
    {
        $this->versions[] = $this->cast($item);

        return $this;
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->versions[$offset]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $offset
     * @return \CupOfTea\SemVer\Contracts\Version
     */
    public function offsetGet($offset): VersionContract
    {
        return $this->versions[$offset];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        try {
            if (is_null($offset)) {
                $this->versions[] = $this->cast($value);
            } else {
                $this->versions[$offset] = $this->cast($value);
            }
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('The value must be a valid version string or instance of \CupOfTea\SemVer\Contracts\Version');
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->versions[$offset]);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(function (VersionContract $version) {
            return $version->jsonSerialize();
        }, $this->all());
    }

    /**
     * Cast the given version to a Version instance if it isn't already.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version|string|null  $version
     * @return \CupOfTea\SemVer\Contracts\Version|null
     */
    protected function cast($version): ?VersionContract
    {
        // value($version)
        $version = $version instanceof Closure ? $version() : $version;
        
        if (is_null($version)) {
            return null;
        }

        if (! $version instanceof VersionContract) {
            $version = new Version($version);
        }

        return $this->normalizePrefix($version);
    }

    /**
     * Convert the given versions to the appropriate implementation according to the Collection setting.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version[]|\CupOfTea\SemVer\Contracts\Version  $version
     * @return \CupOfTea\SemVer\Contracts\Version[]|\CupOfTea\SemVer\Contracts\Version
     */
    protected function normalizePrefix($version)
    {
        if ($this->usePrefix || $this->dontUsePrefix) {
            $implementation = $this->usePrefix ? PrefixedVersion::class : UnprefixedVersion::class;

            if (is_array($version)) {
                return $this->mapTo($version, $implementation);
            }

            return $this->convertVersionTo($version, $implementation);
        }

        return $version;
    }


    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function useAsCallable($value): bool
    {
        return ! is_string($value) && is_callable($value);
    }

    /**
     * Map the versions array to a specific Version implementation.
     *
     * @param  array  $versions
     * @param  string  $implementation
     * @return \CupOfTea\SemVer\Contracts\Version[]
     */
    private function mapTo(array $versions, string $implementation): array
    {
        return array_map(function ($version) use ($implementation) {
            if (! $version instanceof VersionContract) {
                throw new InvalidArgumentException('The versions array must only contain instances of \CupOfTea\SemVer\Contracts\Version');
            }

            return $this->convertVersionTo($version, $implementation);
        }, $versions);
    }

    /**
     * Convert a given Version to a given implementation.
     *
     * @param  \CupOfTea\SemVer\Contracts\Version  $version
     * @param  string  $implementation
     * @return \CupOfTea\SemVer\Contracts\Version
     */
    private function convertVersionTo(VersionContract $version, string $implementation): VersionContract
    {
        $interfaces = class_implements($implementation);

        if (! $interfaces || ! in_array(VersionContract::class, $interfaces)) {
            throw new InvalidArgumentException('The given implementation must implement \CupOfTea\SemVer\Contracts\Version');
        }

        if ($version instanceof $implementation) {
            return $version;
        }

        if ($version instanceof ConvertableVersion) {
            return $version->convertTo($implementation);
        }

        if ($implementation instanceof VersionWithMutablePrefix) {
            return $implementation::create(
                $version->getMajor(),
                $version->getMinor(),
                $version->getPatch(),
                $version->getPrerelease(),
                $version->getBuild(),
                $version->hasPrefix(),
            );
        }

        return $implementation::create(
            $version->getMajor(),
            $version->getMinor(),
            $version->getPatch(),
            $version->getPrerelease(),
            $version->getBuild(),
        );
    }

    /**
     * Create a new Collection instance with the given versions, and the same prefix settings as this instance.
     *
     * @param  array  $versions
     * @return \CupOfTea\SemVer\Collection
     */
    private function newFromThis(array $versions): Collection
    {
        $new = new static($versions);

        if ($this->usePrefix) {
            return $new->withPrefix();
        }

        if ($this->dontUsePrefix) {
            return $new->withoutPrefix();
        }

        return $new;
    }
}