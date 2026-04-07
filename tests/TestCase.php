<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public $admin;
    public $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = \App\Models\Admin::findOrFail(1);
        $this->client = factory(\App\Models\Client::class)->create();
    }
}
