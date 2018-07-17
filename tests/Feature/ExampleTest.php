<?php

namespace Tests\Feature;

use Tests\TestCase;
use /** @noinspection PhpUnusedAliasInspection */
    Illuminate\Foundation\Testing\WithoutMiddleware;
use /** @noinspection PhpUnusedAliasInspection */
    Illuminate\Foundation\Testing\DatabaseMigrations;
use /** @noinspection PhpUnusedAliasInspection */
    Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
