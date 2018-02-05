Dataset
=======

Easier for developer to setup data-set for test cases.

## Dump data

```
# GET #staff/devel/php

<?php
echo \go1\util_dataset\staff\dumper\AccountDumper::dump($app['go1.client.es'], $portalId = 500592);
echo \go1\util_dataset\staff\dumper\EnrolmentDumper::dump($app['go1.client.es'], $portalId);
echo \go1\util_dataset\staff\dumper\LoDumper::dump($app['go1.client.es'], $portalId);
```

## Import data

```php
<?php

class MyTest extends \PHPUnit\Framework\TestCase {
    use \go1\util_dataset\ElasticSearchJsonImportingTrait;
    
    public function test() {
        /** @var \go1\app\App $app */
        $app = $this->getApp();
       
        $portalId = 500592;
        $jsonDirectory = 'fixtures/500592/';
        $this->import($app['dbs']['go1'], $app['go1.client.es'], \go1\util\es\Schema::portalIndex($portalId), $app['accounts_name'], $jsonDirectory);
    }
}
```
