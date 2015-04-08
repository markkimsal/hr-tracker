HR Tracker
=====

Installation
=====
```bash
composer install
npm install
./node_modules/.bin/grunt less
```

Make a local db connection by editing etc/dsn.local.php
```php
<?php
$dsnList = array(
'default.dsn' => 'mysql://root:mysql@127.0.0.1:3306/hrtracker_test'
);
```

Screenshots
======
![Dashboard View](http://markkimsal.github.io/ss/Dashboard-HR-Tracker%202015-04-08%2017-28-46.png)
