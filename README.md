# belongsToThrough [![](https://img.shields.io/travis/znck/belongs-to-through.svg)](https://travis-ci.org/znck/belongs-to-through) [![](https://img.shields.io/packagist/v/znck/belongs-to-through.svg)](https://packagist.org/packages/znck/belongs-to-through) 

Adds belongsToThrough relation to laravel models

## Installation

First, pull in the package through Composer.

```js
"require": {
    "znck/belongs-to-through": "~2.0"
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

Sometimes you may want to use a foreign key that doesn't follow Eloquent's foreign key  conventions (i.e the singular version of the table name appended by `_id`). 

Following on from the previous example, let's say we named the foreign key in the in the district table something different.

```
cities
    id - integer
    name - string
    district_id - integer
    
districts
    id - integer
    name - string
    national_state_id - integer <-- not following convention (state_id)

states
    id - integer
    name - string
    country_id - integer
    
countries
    id - integer
    name - string
```

As you can see the `districts` table has a `national_state_id` column as apposed to `state_id` so we need to update our relationship to account for this. 

Instead of passing the reference to the model for the through relationships you can to pass an array with two elements: the model reference and the name of the foreign key. This now informs the relationship it should use a customised foreign key for this model.

```php
Class City extends Model {
	use \Znck\Eloquent\Traits\BelongsToThrough;
	
	public function country() {
		return $this->belongsToThrough(Country::class, [[State::class, 'national_state_id'], District::class]);
	}
}
```