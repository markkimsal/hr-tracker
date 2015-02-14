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
