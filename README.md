# belongsToThrough [![](https://img.shields.io/travis/znck/belongs-to-through.svg)](https://travis-ci.org/znck/belongs-to-through) [![](https://img.shields.io/github/release/znck/belongs-to-through.svg)](https://github.com/znck/belongs-to-through/releases) [![](https://img.shields.io/packagist/v/znck/belongs-to-through.svg)](https://packagist.org/packages/znck/belongs-to-through) [![](https://img.shields.io/packagist/dt/znck/belongs-to-through.svg)](https://packagist.org/packages/znck/belongs-to-through)  [![](https://img.shields.io/packagist/l/znck/belongs-to-through.svg)](http://znck.mit-license.org) [![](https://www.codacy.com/project/badge/005c3669e57442a198f3a4ffe5e5c9e2)](https://www.codacy.com/app/hi_3/belongs-to-through)

Adds belongsToThrough relation to laravel models

## Installation

First, pull in the package through Composer.

```js
"require": {
    "znck/belongs-to-through": "dev"
}

## Usage

Within your eloquent model class add following line

```php
	use \Znck\Eloquent\Relations\BelongsToThroughTrait;
```