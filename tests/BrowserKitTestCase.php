<?php
namespace Tests;

use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

abstract class BrowserKitTestCase extends BaseTestCase
{
    use CreatesApplication, TestHelpers;

    public $baseUrl = 'http://apkip-tpv.dev';
}
