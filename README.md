# ✍ `php-activerecord-utils`
**Simple utilities that make writing `php-activerecord` queries more convenient.**

[![Latest Stable Version](https://poser.pugx.org/roydejong/php-activerecord-utils/v/stable)](https://packagist.org/packages/roydejong/php-activerecord-utils)
[![Build Status](https://travis-ci.org/roydejong/php-activerecord-utils.svg?branch=master)](https://travis-ci.org/roydejong/php-activerecord-utils)
[![License](https://poser.pugx.org/roydejong/php-activerecord-utils/license)](https://packagist.org/packages/roydejong/php-activerecord-utils)

## Getting started
Add this library as a [Composer](https://getcomposer.org/) dependency:

    composer require roydejong/php-activerecord-utils

Once included, you'll be able to autoload the desired classes from the `ActiveRecordUtils\` namespace.

## Conditions
`ActiveRecordUtils\Composers\Conditions` lets you elegantly compose readable `conditions` parameters for activerecord queries.

### Basic usage

```
<?php

use ActiveRecordUtils\Composers\Conditions;

Conditions::make()
    ->where('employee_id = ?', 123)
    ->or('login_id = ?', 123)
    ->andWhere('is_enabled = 1')
    ->value();

// Returns: ["(employee_id = ? OR login_id = ?) AND (is_enabled = 1)", 123, 123]
```

### Features

- 📝 **Better syntax:** Programmatically compose your WHERE clauses with a syntax that's easier to read and more convenient to maintain.
- ➕ **Easy grouping:** Use `andWhere()`, `orWhere()` to start a new parentheses group, or use `and()`, `or()` to add another condition to he current group.
- ✅ **Auto validation:** Issues like wrong parameter count are automatically detected and produce convenient and readable error messages.
