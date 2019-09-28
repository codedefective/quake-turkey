Codedefective, QuakeTurkey API
=======================

"QuakeTurkey" you can follow the recent earthquake in Turkey and 
filter data.

- Data are collected from **Boğaziçi University Kandilli Observatory AND 
Earthquake Research Institute**.

- You can list up to 500 data.
- You can set the data limit.
- You can group data by Date and Location.
- You can adjust the data by date, location and size.


## Installing QuakeTurkey

The recommended way to install QuakeTurkey is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of QuakeTurkey:

```bash
php composer.phar require codedefective/quakeTurkey
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update QuakeTurkey using composer:

 ```bash
php composer.phar update
 ```

### Quake Turkey on Using

```php
require 'vendor/autoload.php';

//if you're working with the clone you should add this
require 'inc.php';
use codedefective\quakeTurkey;

$quakes = new quakeTurkey();

//All data (max 500)
$quakes->getList();

//Limited data (ex:15)
$quakes->limit(15);

//To group by location;
$quakes->groupByLocation()->getList();
//json response;
$quakes->groupByLocation()->toJson()->getList();

//To group by date;
$quakes->groupByDate()->getList();
//json response;
$quakes->groupByDate()->toJson()->getList();

//To sort by date;
$quakes->sortByDate()->getList();
//json response;
$quakes->sortByDate()->toJson()->getList();

//To sort by location;
$quakes->sortByLocation()->getList();
//json response;
$quakes->sortByLocation()->toJson()->getList();

//To sort by size;
$type = 'ml'; // default type ml (ml, md,mw)
$quakes->sortBySize($type)->getList();
//json response;
$quakes->sortBySize()->toJson()->getList();

//graph (experimental)
$quakes->limit(6)->createGraphic();
```