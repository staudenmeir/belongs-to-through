# BelongsToThrough

[![CI](https://github.com/staudenmeir/belongs-to-through/actions/workflows/ci.yml/badge.svg)](https://github.com/staudenmeir/belongs-to-through/actions/workflows/ci.yml?query=branch%3Amain)
[![Code Coverage](https://codecov.io/gh/staudenmeir/belongs-to-through/graph/badge.svg?token=Z4KscVFWIE)](https://codecov.io/gh/staudenmeir/belongs-to-through)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%2010-brightgreen.svg?style=flat)](https://github.com/staudenmeir/belongs-to-through/actions/workflows/static-analysis.yml?query=branch%3Amain)
[![Latest Stable Version](https://poser.pugx.org/staudenmeir/belongs-to-through/v/stable)](https://packagist.org/packages/staudenmeir/belongs-to-through)
[![Total Downloads](https://poser.pugx.org/staudenmeir/belongs-to-through/downloads)](https://packagist.org/packages/staudenmeir/belongs-to-through/stats)
[![License](https://poser.pugx.org/staudenmeir/belongs-to-through/license)](https://github.com/staudenmeir/belongs-to-through/blob/main/LICENSE)

This inverse version of `HasManyThrough` allows `BelongsToThrough` relationships with unlimited intermediate models.

Supports Laravel 5.0+.

## Installation

    composer require staudenmeir/belongs-to-through:"^2.5"

Use this command if you are in PowerShell on Windows (e.g. in VS Code):

    composer require staudenmeir/belongs-to-through:"^^^^2.5"

## Versions

| Laravel | Package |
|:--------|:--------|
| 11.x    | 2.16    |
| 10.x    | 2.13    |
| 9.x     | 2.12    |
| 8.x     | 2.11    |
| 7.x     | 2.10    |
| 6.x     | 2.6     |
| 5.x     | 2.5     |

## Usage

- [Custom Foreign Keys](#custom-foreign-keys)
- [Custom Local Keys](#custom-local-keys)
- [Table Aliases](#table-aliases)
- [Soft Deleting](#soft-deleting)

Consider this `HasManyThrough` relationship:  
`Country` → has many → `User` → has many → `Post`

```php
class Country extends Model
{
    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class);
    }
}
```

Use the `BelongsToThrough` trait in your model to define the inverse relationship:  
`Post` → belongs to → `User` → belongs to → `Country`  

```php
class Post extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;

    public function country(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(Country::class, User::class);
    }
}
```

You can also define deeper relationships:  
`Comment` → belongs to → `Post` → belongs to → `User` → belongs to → `Country`

Supply an array of intermediate models as the second argument, from the related (`Country`) to the parent model (`Comment`):  

```php
class Comment extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;

    public function country(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(Country::class, [User::class, Post::class]);
    }
}
```

### Custom Foreign Keys

You can specify custom foreign keys as the fifth argument:

```php
class Comment extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;

    public function country(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(
            Country::class,
            [User::class, Post::class], 
            foreignKeyLookup: [User::class => 'custom_user_id']
        );
    }
}
```

### Custom Local Keys

You can specify custom local keys for the relations:

`VendorCustomerAddress` → belongs to → `VendorCustomer` in `VendorCustomerAddress.vendor_customer_id`
`VendorCustomerAddress` → belongs to → `CustomerAddress` in `VendorCustomerAddress.address_id`

You can access `VendorCustomer` from `CustomerAddress` by the following

```php
class CustomerAddress extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;

    public function vendorCustomer(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(
            VendorCustomer::class,
            VendorCustomerAddress::class,
            foreignKeyLookup: [VendorCustomerAddress::class => 'id'],
            localKeyLookup: [VendorCustomerAddress::class => 'address_id'],
        );
    }    
}
```

### Table Aliases

If your relationship path contains the same model multiple times, you can specify a table alias (Laravel 6+):

```php
class Comment extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;

    public function grandparent(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(
            Comment::class,
            Comment::class . ' as alias',
            foreignKeyLookup: [Comment::class => 'parent_id']
        );
    }
}
```

Use the `HasTableAlias` trait in the models you are aliasing:

```php
class Comment extends Model
{
    use \Znck\Eloquent\Traits\HasTableAlias;
}
```

### Soft Deleting

By default, soft-deleted intermediate models will be excluded from the result. Use `withTrashed()` to include them:

```php
class Comment extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;

    public function country(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(Country::class, [User::class, Post::class])
            ->withTrashed('users.deleted_at');
    }
}

class User extends Model
{
    use SoftDeletes;
}
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CODE OF CONDUCT](.github/CODE_OF_CONDUCT.md) for details.

## Credits

- [Rahul Kadyan](https://github.com/znck)
- [Danny Weeks](https://github.com/dannyweeks)
- [All Contributors](../../contributors)
