<?php

namespace CD\Models;

use CD\Config\Config;
use CD\Core\DB\BaseDBModel;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use DateTime;
use Exception;

class SLP extends BaseDBModel
{
    protected $data;
    protected string $contract;
    static protected CoinGeckoClient $client;

    public function __construct()
    {
        parent::__construct();
        self::$client = new CoinGeckoClient();
        $this->contract = strtolower(Config::SLP_CONTRACT_ADDRESS);
        $s = $this->db->prepare("
                select 
                SLPCacheData as data,
                SLPCacheDateCached as cache_date,
                SLPCacheRate as last_known_rate
                from {$this->tableName()} 
                order by {$this->primaryKey()} 
                desc limit 1
        ");
        $s->execute();
        if (!$data = $s->fetch()) {
            $data = $this->getLatestAndCache();
        }
        $this->data = $data;
    }

    public function tableName(): string
    {
        return 'SLPCaches';
    }

    public function columns(): array
    {
        return ['*'];
    }

    public function primaryKey(): string
    {
        return 'SLPCacheID';
    }

    /**
     * @throws Exception
     */
    public function getData()
    {
        $data = $this->data;
        if ($data['data']) {
            $data['data'] = is_string($data['data']) ? json_decode($data['data']) : $data['data'];
            $cache_date = new DateTime($data['cache_date']);
            $d = $cache_date->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($d->i >= 30) {
                $t = $this->getLatestAndCache();
                if ($t['data'] && $t['last_known_rate'] != 0) {
                    $data = $t;
                }
            }
        }
        return $data;
    }

    /**
     * @throws Exception
     */
    public function getLatestRate(): array
    {
        try {
            $data['rate'] = self::$client->simple()->getTokenPrice('ethereum', $this->contract, 'php')[$this->contract]['php'];
        } catch (Exception $e) {
            if ($this->data['last_known_rate']) {
                $data['rate'] = $this->data['last_known_rate'];
            } else {
                throw new Exception('Unable to connect to CoinGecko.');
            }
        }
        $data['date'] = date('Y-m-d H:i:s');
        return $data;
    }

    /**
     * @throws Exception
     */
    public function getLatestMarketChart(): array
    {
        $client = self::$client;
        try {
            $data['data'] = $client->contract()->getMarketChart('ethereum', $this->contract, 'php', '1')['prices'];
            $data['last_known_rate'] = round($data['data'][count($data['data']) - 1][1], 2);
        } catch (Exception $e) {
            throw new Exception('Unable to connect to CoinGecko.');
        }
        $data['cache_date'] = date('Y-m-d H:i:s');
        return $data;
    }

    /**
     * @throws Exception
     */
    protected function getLatestAndCache(): array
    {
        try {
            $data = $this->getLatestMarketChart();
            $this->data = $data;
            $this->saveToDB();
        } catch (Exception $e) {
            $data['cache_date'] = date('Y-m-d H:i:s');
            $data['data'] = [];
            $data['last_known_rate'] = 0;
        }
        return $data;
    }

    protected function saveToDB(): bool
    {
        if ($this->data) {
            $s = $this->db->prepare("insert into {$this->tableName()} (SLPCacheData, SLPCacheRate, SLPCacheDateCached) values (?, ?, ?)");
            return $s->execute([json_encode($this->data['data']), $this->data['last_known_rate'], $this->data['cache_date']]);
        }
        return false;
    }
}