<?php

namespace App\CMS\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Torann\GeoIP\Services\AbstractService;

class GEOIP extends AbstractService
{
    /**
     * Http client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * The "booting" method of the service.
     *
     * @return void
     */
    public function boot()
    {
        $this->client = new Client([
            'base_uri' => config('cms-client.geoip_url'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($ip)
    {
        $response = $this->client->get('geoip', [
            'headers' => [
                'Quoapi-Appid' => config('cms-client.geoip_app_id'),
                'Quoapi-Apptoken' => config('cms-client.geoip_app_token')
            ],
            'query' => [
                'ipaddr' => $ip,
            ]
        ]);

        $data = null;

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
        } else {
            throw new Exception('Request failed.');
        }

        // Parse body content
        $json = json_decode($data, false);

        $defaultIsoCode = config('cms-client.default_iso_code', 'US');
        $defaultCode = config('cms-client.default_language_code', 'EN');
        $defaultName = config('cms-client.default_language_name', 'English');

        return $this->hydrate([
            'ip' => $ip,
            'iso_code' => isset_not_empty($json->data->code, $defaultIsoCode),
            'continent_code' => isset_not_empty($json->data->continent_code),
            'continent_name' => isset_not_empty($json->data->continent_name),
            'code' => isset_not_empty($json->data->code, $defaultCode),
            'name' => isset_not_empty($json->data->name, $defaultName)
        ]);
    }
}