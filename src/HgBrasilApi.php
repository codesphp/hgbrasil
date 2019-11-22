<?php namespace CodesPhp\HgBrasil;

use GuzzleHttp\Client;
use CodesPhp\Support\Arr;
use CodesPhp\Support\Attrs;

class HgBrasilApi
{
    /**
     * Key.
     * @var string|null
     */
    protected $key;

    /**
     * Client request.
     * @var Client
     */
    protected $client;

    /**
     * string 
     */
    protected $endpoints = [
        'finance.quotations' => 'https://api.hgbrasil.com/finance/quotations?format=json',
        'geoip'              => 'https://api.hgbrasil.com/geoip?format=json',
    ];

    /**
     * Contrutor class.
     * 
     * @param string $key
     */
    public function __construct($key = null)
    {
        $this->key = $key;        

        $this->client = new Client([]);
    }

    /**
     * Request base.
     * 
     * @param string $endpoint
     * @param array $params
     * @return mixed
     */
    protected function request($endpoint, $params = [])
    {
        $uri = $this->makeUri($endpoint, $params);

        $respose = $this->client->request('get', $uri);
        $json = json_decode(trim($respose->getBody()), true);

        return $json;
    }

    /**
     * Get currency info.
     * 
     * @param string $isoCurrency
     * @return Attrs|null
     */
    public function getCurrency($isoCurrency)
    {
        $isoCurrency = strtoupper($isoCurrency);

        $data = $this->request('finance.quotations', ['mode' => 'currencies']);
        if (is_array($data)) {
            return Attrs::make(Arr::get($data, 'results.currencies.' . $isoCurrency));
        }

        return null;
    }

    /**
     * Get geoip info.
     * 
     * @param string $ipAddress
     * @return Attrs
     */
    public function getGeoIP($ipAddress)
    {
        $data = $this->request('geoip', ['address' => $ipAddress, 'precision' => false]);

        if (is_array($data)) {
            return Attrs::make(Arr::get($data, 'results'));
        }

        return null;

    }

    /**
     * Make URI.
     * 
     * @param string $endpoint
     * @param array $params
     * @return string
     */
    protected function makeUri($endpoint, $params = [])
    {
        $uri = $this->endpoints[$endpoint];

        // Check key
        if (! is_null($this->key)) {
            $params['key'] = $this->key;
        }

        // Params
        foreach ($params as $k => $v) {
            $uri .= '&' . $k  . '=' . urlencode($v);
        }

        return $uri;
    }
}