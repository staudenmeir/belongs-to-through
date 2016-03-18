Belongs-To-Through
==================
Inverse of HasManyThrough relation is missing from [Laravel](https://laravel.com/)'s ORM. Belongs-To-Through extends [Eloquent](https://laravel.com/docs/master/eloquent) ORM with  belongsToThrough relation.

![Belongs-To-Through](cover.png)

<p align="center">
  <a href="https://styleci.io/repos/36823627">
    <img src="https://styleci.io/repos/36823627/shield" alt="StyleCI Status" />
  </a>
  <a href="https://circleci.com/gh/znck/belongs-to-through">
    <img src="https://circleci.com/gh/znck/belongs-to-through.svg?style=svg" alt="Build Status" />
  </a>
  <a href="https://coveralls.io/github/znck/belongs-to-through?branch=master">
    <img src="https://coveralls.io/repos/github/znck/belongs-to-through/badge.svg?branch=master&style=flat-square" alt="Coverage Status" />
  </a>
  <a href="LICENSE">
    <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License" />
  </a>
  <a href="https://packagist.org/packages/znck/belongs-to-through">
    <img src="https://img.shields.io/packagist/v/znck/belongs-to-through.svg?style=flat-square" alt="Packagist" />
  </a>
  <a href="https://github.com/znck/belongs-to-through/releases">
    <img src="https://img.shields.io/github/release/znck/belongs-to-through.svg?style=flat-square" alt="Latest Version" />
  </a>

  <a href="https://github.com/znck/belongs-to-through/issues">
    <img src="https://img.shields.io/github/issues/znck/belongs-to-through.svg?style=flat-square" alt="Issues" />
  </a>
</p>

## Installation

Either [PHP](https://php.net) 5.6+ is required.

To get the latest version of Belongs-To-Through, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require znck/belongs-to-through
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "znck/belongs-to-through": "^2.2"
    }
}
```

## Usage

Within your eloquent model class add following line

```php
class User extends Model {
    use \Znck\Eloquent\Traits\BelongsToThrough;
    ...
}
```

## Example:
Consider a blog application. In this app, a country can have many users and a user can have many articles. So, `hasManyThrough` provides easy way to access articles from a country.

```php
class Country extends Model {
    use \Znck\Eloquent\Traits\BelongsToThrough;

    public function articles () {
        return $this->hasManyThrough(Article::class, User::class);
    }
}
```

If we are accessing the country of the article, then we have to use `$article->user->country`.

```php
Class Article extends Model {
    use \Znck\Eloquent\Traits\BelongsToThrough;

    public function country() {
        return $this->belongsToThrough(Country::class, User::class);
    }
}
```

Now, the magic: `$article->country`

Going deeper, `City` -> `District` -> `State` -> `Country`

```php
Class City extends Model {
	use \Znck\Eloquent\Traits\BelongsToThrough;

	public function country() {
		return $this->belongsToThrough(Country::class, [State::class, District::class]);
	}
}
```


## License

Plug is licensed under [The MIT License (MIT)](LICENSE).
