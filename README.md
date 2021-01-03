# PHP SemVer

A Semantic Version library for PHP.

## Installation

### Prerequisites

To use PHP SemVer, you will first need to install [Composer](https://getcomposer.org/doc/00-intro.md) if you haven't already.

### Install via Composer

To install PHP SemVer, simply require it with Composer.

```
$ composer require cupoftea/semver ^0.0.0
```

## Usage

### Initialising

There are several ways to create a `Version` instance.

 - It can be initialised without any arguments, which will set the version to `v0.1.0` by default.
 - It can be initialised by providing a version string.
 - It can be initialised by providing an incomplete version string.
 - It can be initialised by calling the static `create()` method and providing each version component individually.

```php
$version = new Version(); // v0.1.0
$version = new Version('v1.0'); // v1.0.0
$version = new Version('v1.3.1-beta.2+dba5ed6');
$version = Version::create(1, 2, 0, null, null); // v1.2.0
$version = Version::create(0, 0, 1, 'beta.1', 'cdcc492'); // v0.0.1-beta.1+cdcc492
```

### Access

All version components can be accesse via their respective get and set methods. They are also available as read-only properties. The prerelease and build components also provide ways to check their status or unset them.

The full version string can be retrieved via the `getVersion()` method, or casting the instance to a string.

_**Note:** Setting a version component **will not** automatically reset any lower order components!_

```php
$v = new Version('v1.3.1-beta.2+dba5ed6');

// Getting components
$v->hasPrefix(); // true
$v->getMajor(); // 1
$v->getMinor(); // 3
$v->getPatch(); // 1
$v->isPrerelease(); // true
$v->getPrerelease(); // beta.2
$v->hasBuild(); // true
$v->getBuild(); // dba5ed6

// Setting components
$v->setMajor(3); // v3.3.1-beta.2+dba5ed6
$v->setMinor(1); // v3.1.1-beta.2+dba5ed6
$v->setPatch(23); // v3.1.23-beta.2+dba5ed6
$v->setPrerelease('rc.1'); // v3.1.23-rc.1+dba5ed6
$v->setBuild('b93ca22'); // v3.1.23-rc.1+b93ca22

// Clearing pre-release and build meta.
$v->unsetPrerelease(); // v3.1.23+dba5ed6
$v->unsetBuild(); // v3.1.23

// Retrieving the version string
$v->getVersion(); // v3.1.23
```

### Bumping and Releasing.

A version instance that has a pre-release and/or build meta can be 'released', clearing the pre-release and build meta using the `release()` method.

The major, minor, and patch components can be bumped using their respective bump methods. Optionally, you can pass a value to bump the version component by.

_**Note:** Bumping a version component **will** automatically reset any lower-order components, including pre-release and build meta!_

```php
$v = new Version('v1.2.4-rc.2+8d63591');

$v->release(); // v1.2.4
$v->bumpPatch(); // v1.2.5
$v->bumpMinor(2); // v1.5.0
$v->bumpMajor(); // v2.0.0
```

### The 'v' Prefix

The `Version` class automatically detects if the string used for its initialisation contained a 'v' prefix, and its state can be read using the `hasPrefix()` method. When converting or casting the instance to a string, this state determines whether a 'v' prefix gets added.

On the `Version` class, there are various ways to change this state. Additionally, when using the `create()` method to create a new instance, you can pass the prefix state as a 6th argument.

_**Note:** This functionality is not included by default in the `Version` interface. Use the `VersionWithMutablePrefix` interface for type-hinting instead._

```php
$v = Version::create(2, 4, 1, null, null, false); // 2.4.1

$v->withPrefix(); // v2.4.1
$v->withoutPrefix(); // 2.4.1

$v->setPrefix(true); // v2.4.1
$v->setPrefix(false); // 2.4.1
```

If you want to ensure a `Version` instance will always or never add a prefix when being cast to string, you can use the `PrefixedVersion` or `UnprefixedVersion` classes respectively. PHP SemVer also provides a handy way to convert a `Version` instance to one of these classes that have an immutable prefix.

```php
$v = new Version('v1.3.9');
$pv = new PrefixedVersion('2.5.6');
$uv = new UnprefixedVersion('v0.2.3');

$v->toPrefixed() instanceof PrefixedVersion; // true
$v->toUnprefixed() instanceof UnprefixedVersion; // true

$pv->getVersion(); // v2.5.6
$uv->getVersion(); // 0.2.3
```

### Comparing Versions

PHP SemVer includes a `Compare` class to compare versions against eachother. You can provide this class with `Version` instances or strings to compare.

Comparisons ignore the build meta of a version, because there is no logical way to compare versions that only differ in build meta. This should never be the case anyway.

```php
$v1 = new Version('v1.2.3');
$v2 = new Version('2.3.1');

Compare::eq($v1, $v2); // false
Compare::gt($v1, $v2); // false
Compare::gte($v1, '1.2.3'); // true
Compare::lt($v1, '0.2.1'); // false
Compare::lte($v1, $v2); // true

Compare::gt('v1.0.0-beta.1', 'v1.0.0-alpha.1'); // true
Compare::gt('v1.0.0-beta.2', 'v1.0.0-rc.3'); // false

Compare::eq('v1.0.0+acdc0f7', 'v1.0.0+de3f0a0'); // true

// The `comp()` method acts as the 'spaceship' operator `<=>` and can be useful for sorting operations.
Compare::comp('1.0.0', '2.0.0'); // -1
Compare::comp('1.0.0', '1.0.0'); // 0
Compare::comp('2.0.0', '1.0.0'); // 1
```

You can also quickly compare a version instance against another using its own comparison methods.

```php
$v = new Version('v1.2.3');

$v->eq('1.3.2'); // false
$v->gt('0.1.2'); // true
$v->gte(new Version('v3.2.3')); // false
$v->lt('1.2.3'); // false
$v->lte('1.2.3'); // true
```

For operations comparing sets of versions, like sorting or determining the highest version number in a set, the `Collection` class allows you to do this with ease.

Collections are array-accessible, and implement the `ArrayAccess`, `Countable`, `IteratorAggregate`, and `JsonSerializable` interfaces.

The `Filter` class provides some methods to easily create Comparison callbacks for Filtering collections.

```php
$versions = new Collection(['v1', '1.2.1', 'key' => '2.3.1', '2.3.1', '1.2.1-beta', '0.3.1-beta.2', '1.2.1-alpha']);

$versions['key']; // 2.3.1
$versions->first(); // v1.0.0
$versions->last(); // 1.2.1-alpha
$versions->min(); // 0.3.1-beta.2
$versions->max(); // 2.3.1
$versions->search('2.3.1'); // 'key'

$versions->count(); // 7
count($versions); // 7

$versions->all(); // ['v1', '1.2.1', 'key' => '2.3.1', '2.3.1', '1.2.1-beta', '0.3.1-beta.2', '1.2.1-alpha']
$versions->values()->all(); // ['v1', '1.2.1', '2.3.1', '2.3.1', '1.2.1-beta', '0.3.1-beta.2', '1.2.1-alpha']

$versions->withPrefix()->values()->all(); // ['v1.0.0', 'v1.2.1', 'v2.3.1', 'v2.3.1', 'v1.2.1-beta', 'v0.3.1-beta.2', 'v1.2.1-alpha']
$versions->withoutPrefix()->values()->all(); // ['1.0.0', '1.2.1', '2.3.1', '2.3.1', '1.2.1-beta', '0.3.1-beta.2', '1.2.1-alpha']

$versions->reverse()->values()->all(); // ['1.2.1-alpha', '0.3.1-beta.2', '1.2.1-beta', '2.3.1', '2.3.1', '1.2.1', '1.0.0']
$versions->sort()->values()->all(); // ['0.3.1-beta.2', 'v1.0.0', '1.2.1-alpha', '1.2.1-beta', '1.2.1', '2.3.1', '2.3.1']
$versions->sortDesc()->values()->all(); // ['2.3.1', '2.3.1', '1.2.1', '1.2.1-beta', '1.2.1-alpha', 'v1.0.0', '0.3.1-beta.2']

$filtered = $versions->filter(function ($v) {
    return $v->getMajor() === 1;
});
$filtered->values()->all(); // ['1.0.0', '1.2.1', '1.2.1-beta', '1.2.1-alpha']

$versions->filter(Filter::eq('2.3.1'))->values()->all(); // ['2.3.1', '2.3.1']
$versions->filter(Filter::gte('1.2.1'))->values()->all(); // ['1.2.1', '2.3.1', '2.3.1']

$versions->first(Filter::gt('1.0.1')); // 1.2.1
$versions->sort()->last(Filter::gt('1.0.1')); // 2.3.1
```

Below are some other Collection methods for basic Collection management.

```php
$versions = new Collection(['v1', '2']);
$versions->withPrefix(); // This ensures all current and future versions in the collection will have the 'v' prefix.
$versions->withoutPrefix(); // The ensures all current and future versions in the collection will not have the 'v' prefix.

// Getting, setting, and unsetting
$versions = new Collection(['stable' => 'v1', 'beta' => 'v1.0.0-beta.1', 'rc' => 'v1.0.0-rc.3']);
$versions->get('beta'); // v1.0.0-beta.1
$versions->put('rc', 'v1.0.0-rc.4'); // ['stable' => 'v1', 'beta' => 'v1.0.0-beta.1', 'rc' => 'v1.0.0-rc.4']
$versions->forget('beta', 'rc'); // ['stable' => 'v1.0.0']
$versions->add('v2')->values(); // ['v1.0.0', 'v2.0.0'];
$versions->push('v3', '4')->values(); // ['v1.0.0', 'v2.0.0', 'v3.0.0', 'v4.0.0'];

// Get & remove
$versions->shift(); // v1.0.0
$versions->pop(); // v4.0.0
$versions->all(); // ['v2.0.0', 'v3.0.0'];

// Appending another array or collection's values onto the collection.
$versions->concat(['beta' => '1.0.0-beta.1', 'rc' => 'v1.0.0-rc.3']); // ['v2.0.0', 'v3.0.0', '1.0.0-beta.1', 'v1.0.0-rc.3']

// Joining
$versions = new Collection(['alpha' => '1.0.0-alpha.1']);
$versions->merge(['alpha' => '1.0.0-alpha.2', 'beta' => '1.0.0-beta.3']); // ['alpha' => '1.0.0-alpha.2', 'beta' => '1.0.0-beta.3']
$versions->union(['beta' => '1.0.0-beta.1', 'rc' => '1.0.0-rc.4']); // ['alpha' => '1.0.0-alpha.2', 'beta' => '1.0.0-beta.3', 'rc' => '1.0.0-rc.4']

// Looping
foreach ($versions as $version) {
    $version->bumpMinor();
}

// Slicing
$versions = new Collection(['v1', 'v2', 'v3', 'v4']);
$versions->slice(2)->values()->all(); // ['v3.0.0', 'v4.0.0']
$versions->slice(1, 2)->values()->all(); // ['v2.0.0', 'v3.0.0']

// Splicing
$versions = new Collection(['v1', 'v2', 'v3', 'v4']);
$versions->splice(2)->values()->all(); // ['v3.0.0', 'v4.0.0']
$versions->values()->all(); // ['v1.0.0', 'v2.0.0']

$versions = new Collection(['v1', 'v2', 'v3', 'v4']);
$versions->splice(1, 2)->values()->all(); // ['v2', 'v3']
$versions->values()->all(); // ['v1', 'v4']

$versions = new Collection(['v1', 'v2', 'v3', 'v4']);
$versions->splice(1, 2, ['v2.2.0', 'v3.3.0'])->values()->all(); // ['v2.0.0', 'v3.0.0']
$versions->values()->all(); // ['v1.0.0', 'v2.2.0', 'v3.3.0', 'v4.0.0']
```

## Extending

### The Interface

The main interface for PHP SemVer is `CupOfTea\SemVer\Contracts\Version`, and any Version implementation must implement this interface. The `Version` interface implements `JsonSerializable` and `Stringable`. The `Stringable` interface is polyfilled for PHP < 8 using `symfony/polyfill-php80`.

You should implement `CupOfTea\SemVer\Contracts\VersionWithMutablePrefix` if your implementation allows changing the version's 'v' prefix.

Lastly, if your implementation can be converted to other Version implementations, you should implement `CupOfTea\SemVer\Contracts\ConvertableVersion` as well. You can easily implement the `convertTo()` method by using the ConvertsVersionImplementations trait `CupOfTea\SemVer\Concerns\ConvertsVersionImplementations`.

```php
/**
 * @method __construct(string $version = 'v0.1.0')
 * @method \CupOfTea\SemVer\Contracts\Version create(int $major = 0, int $minor = 0, int $patch = 0, ?string $prerelease = null, ?string $build = null)
 * @method bool hasPrefix()
 * @method int getMajor()
 * @method void setMajor(int $major)
 * @method int getMinor()
 * @method void setMinor(int $minor)
 * @method int getPatch()
 * @method void setPatch(int $patch)
 * @method bool isPrerelease()
 * @method string|null getPrerelease()
 * @method void setPrerelease(?string $prerelease)
 * @method void unsetPrerelease()
 * @method bool hasBuild()
 * @method string|null getBuild()
 * @method void setBuild(?string $build)
 * @method void unsetBuild()
 * @method \CupOfTea\SemVer\Contracts\Version bumpMajor(int $by = 1)
 * @method \CupOfTea\SemVer\Contracts\Version bumpMinor(int $by = 1)
 * @method \CupOfTea\SemVer\Contracts\Version bumpPatch(int $by = 1)
 * @method \CupOfTea\SemVer\Contracts\Version release()
 * @method string getVersion()
 */
interface Version extends JsonSerializable, Stringable
{
}

/**
 * @method \CupOfTea\SemVer\Contracts\VersionWithMutablePrefix create(int $major = 0, int $minor = 0, int $patch = 0, ?string $prerelease = null, ?string $build = null, bool $hasPrefix = true)
 * @method \CupOfTea\SemVer\Contracts\VersionWithMutablePrefix withPrefix()
 * @method \CupOfTea\SemVer\Contracts\VersionWithMutablePrefix withoutPrefix()
 * @method void setPrefix(bool $prefix)
 */
interface VersionWithMutablePrefix extends Version
{
}

/**
 * @method \CupOfTea\SemVer\Contracts\Version convertTo(string $implementation)
 */
interface ConvertableVersion
{
}
```

### Creating an Implementation

If you intend to create your own Version implementation, it is recommended that you extend the abstract `BaseVersion` class. This allows you to easily overwrite any methods without having to write the entire implementation yourself. 

Two methods of interest to overwrite are `handleMatches()` and `handlePrefix()`. Both methods will receive the regex matches from the version string used to create the instance, and allow you to easily set the initial properties without having to worry about any validation or extracting the version components.

_**Note:** The `handlePrefix()` method is abstract, and must be implemented when extending `CupOfTea\SemVer\BaseVersion`._

```php
class MyVersion extends \CupOfTea\SemVer\BaseVersion
{
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
    protected function handlePrefix(array $matches): void
    {
        $this->prefix = isset($matches['prefix']) && ! empty($matches['prefix']);
    }
}
```
