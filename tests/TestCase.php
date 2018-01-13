<?php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, TestHelpers;

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(\Exception $e) {}
            public function render($request, \Exception $e) {
                throw $e;
            }
        });
    }

    protected function assertValidationErrors(TestResponse $response, $fields)
    {
        $fields = (array) $fields;

        $response->assertStatus(422);

        $messageBag = $response->exception->validator->getMessageBag();

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $messageBag->getMessages());
        }
    }
}
