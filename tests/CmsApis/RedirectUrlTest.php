<?php

namespace Tests\CmsApis;

use App\Api\Models\RedirectUrl;
use App\Api\Models\Site;
use Tests\CmsApiTestCase;

class RedirectUrlTest extends CmsApiTestCase
{
    public function testGetRedirectUrls()
    {
        factory(RedirectUrl::class, 3)->create();
        $urls = RedirectUrl::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/redirectUrls', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $urls
            ]);
    }

    public function testGetRedirectUrlById()
    {
        $url = factory(RedirectUrl::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/redirectUrl/' . $url->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $url->id
                ]
            ]);
    }

    public function testGetRedirectUrlBySourceUrl()
    {
        $url = factory(RedirectUrl::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/redirectUrl/' . $url->source_url, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $url->id,
                    'source_url' => $url->source_url
                ]
            ]);
    }

    public function testStore()
    {
        $params = [
            'source_url' => self::$faker->slug(),
            'destination_url' => self::$faker->url,
            'site_id' => factory(Site::class)->create()->id
        ];
        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/redirectUrl', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $params['site_id'],
                    'source_url' => $params['source_url'],
                    'destination_url' => $params['destination_url']
                ]
            ]);

        $this->assertDatabaseHas('redirect_urls', [
            'site_id' => $params['site_id'],
            'source_url' => $params['source_url'],
            'destination_url' => $params['destination_url']
        ]);
    }

    public function testUpdate()
    {
        /** @var RedirectUrl[]|\Illuminate\Support\Collection $urls */
        $urls = factory(RedirectUrl::class, 3)
            ->create()
            ->each(function ($item) {
                $item->destination_url = 'google.com';
            });
        $data = $urls->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/redirectUrls/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'destination_url' => 'google.com'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('redirect_urls', [
            'destination_url' => 'google.com'
        ]);
    }

    public function testUpdateChangeSiteId()
    {
        $site = factory(Site::class)->create();

        /** @var RedirectUrl[]|\Illuminate\Support\Collection $urls */
        $urls = factory(RedirectUrl::class, 3)
            ->create()
            ->each(function ($item) use ($site) {
                $item->site_id = $site->id;
            });
        $data = $urls->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/redirectUrls/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'site_id' => $site->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('redirect_urls', [
            'site_id' => $site->id
        ]);
    }

    public function testUpdateById()
    {
        $url = factory(RedirectUrl::class)->create();
        $url->destination_url = 'google.com';
        $data = $url->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/redirectUrl/' . $url->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'destination_url' => 'google.com'
                ]
            ]);

        $this->assertDatabaseHas('redirect_urls', [
            'destination_url' => 'google.com'
        ]);
    }

    public function testUpdateByIdChangeSiteId()
    {
        $site = factory(Site::class)->create();
        $url = factory(RedirectUrl::class)->create();
        $url->site_id = $site->id;
        $data = $url->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/redirectUrl/' . $url->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $site->id
                ]
            ]);

        $this->assertDatabaseHas('redirect_urls', [
            'site_id' => $site->id
        ]);
    }

    public function testDelete()
    {
        $urls = factory(RedirectUrl::class, 3)->create();
        $data = $urls->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/redirectUrls', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('redirect_urls', [
            'id' => $urls->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $url = factory(RedirectUrl::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/redirectUrl/' . $url->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('redirect_urls', [
            'id' => $url->id
        ]);
    }
}