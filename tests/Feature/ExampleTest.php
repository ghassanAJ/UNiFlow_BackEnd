<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_root_identifies_the_api(): void
    {
        $this->getJson('/')->assertOk()->assertJsonPath('name', 'UniFlow');
    }
}
