<?php

namespace app\components\api;

use Exception;
use http\Client\Response;
use Yii;
use yii\httpclient\Client;

class CryptoCompare
{
    /** @var string */
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = Yii::$app->params['cryptoCompareApi']['url'];
    }

    /**
     * @param array $altcoinList
     * @param array $currencyList
     * @return array
     */
    public function getMultiPrice(array $altcoinList, array $currencyList): array
    {
        $cache = Yii::$app->cache;
        $key = implode(',', $altcoinList) .'_'. implode(',', $currencyList);
        $prices = $cache->getOrSet($key, function() use ($altcoinList, $currencyList) {
            return $this->requestPrice($altcoinList, $currencyList);
        }, 100);

        return $prices;
    }
    
    /**
     * @param array $altcoinList
     * @param array $currencyList
     * @return array
     */
    protected function requestPrice(array $altcoinList, array $currencyList): array
    {
        $altcoins = implode(',', $altcoinList);
        $currencies = implode(',', $currencyList);
        $url = $this->apiUrl . "pricemulti?fsyms=$altcoins&tsyms=$currencies";
        return $this->priceData($url);
    }

    /**
     * @param string $altcoin
     * @param integer $unixTime
     * @return array
     */
    public function getPriceOnDate(string $altcoin, int $unixTime): array
    {
        $url = $this->apiUrl . "pricehistorical?fsym=$altcoin&tsyms=USD&ts=$unixTime";
        return $this->priceData($url);
    }

    /**
     * @param string $url
     * @return array
     */
    protected function priceData(string $url): array
    {
        try {
            $response = (new Client())->createRequest()->setMethod('GET')->setUrl($url)->send();
            $prices = $response->isOk ?
                ['data' => $response->data, 'success' => true, 'error' => false] :
                ['data' => [], 'success' => false, 'error' => 'Что то пошло не так...'];
        } catch (Exception $e) {
            $prices = ['data' => [], 'success' => false, 'error' => $e->getMessage()];
        }
        return $prices;
    }
}