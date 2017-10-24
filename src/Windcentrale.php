<?php

namespace trizz\WindcentraleApi;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;

class Windcentrale
{
    /**
     * @var \Illuminate\Support\Collection
     */
    private $config;

    /**
     * Windcentrale constructor.
     *
     * @param \Illuminate\Support\Collection $config
     */
    public function __construct(Collection $config)
    {
        $this->config = $config;
    }

    public function getProductionData(int $millId) : ?Collection
    {
        $client = new GuzzleClient();
        $res = $client->request('GET', sprintf($this->config->get('urls')->get('production'), $millId));

        if ($res->getStatusCode() !== 200) {
            return null;
        }

        $productionData = simplexml_load_string($res->getBody());

        $totalWindShares = (int) $productionData->productie->attributes()->winddelen;

        $sumDay = (float) $productionData->productie->subset[0]->attributes()->sum;
        $sumWeek = (float) $productionData->productie->subset[2]->attributes()->sum;
        $sumMonth = (float) $productionData->productie->subset[1]->attributes()->sum;
        $sumYear = (float) $productionData->productie->subset[3]->attributes()->sum;

        $totalDay = $sumDay / $totalWindShares * 1000;
        $totalWeek = $sumWeek / $totalWindShares * 1000;
        $totalMonth = $sumMonth / $totalWindShares * 1000;
        $totalYear = $sumYear / $totalWindShares * 1000;

        return new Collection([
            'day' => $totalDay,
            'week' => $totalWeek,
            'month' => $totalMonth,
            'year' => $totalYear,
        ]);
    }

    public function getMillData(int $millId): ?Collection
    {
        $client = new GuzzleClient();
        $res = $client->request('GET', sprintf($this->config->get('urls')->get('mill_data'), $millId));

        if ($res->getStatusCode() !== 200) {
            return null;
        }

        return new Collection(json_decode($res->getBody(), true));
    }
}