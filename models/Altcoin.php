<?php

namespace app\models;

use app\components\api\CryptoCompare;
use DateTime;
use Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "altcoin".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $full_name
 * @property int $date_start_unix
 * @property string $date_start
 * @property int $sort
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property AltcoinWatcher[] $altcoinWatchers
 * @property AltcoinHistoryData $altcoinLastData
 */
class Altcoin extends ActiveRecord
{
    const BTC = 'btc';
    const ETH = 'eth';
    const LTC = 'ltc';
    const XRP = 'xrp';
    const ATOM = 'atom';
    const XMR = 'xmr';
    const BNB = 'bnb';
    const ZEC = 'zec';
    const ADA = 'ada';
    const DOT = 'dot';
    const RVN = 'rvn';

    const USD = 'USD';
    const RUB = 'RUB';
    const EUR = 'EUR';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'altcoin';
    }

    /** @var int */
    public $start_unixtime;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'full_name', 'date_start'], 'string', 'max' => 255],
            [['name'], 'filter', 'filter' => 'strtoupper', 'skipOnArray' => true],
            [['sort', 'date_start_unix'], 'integer'],
            [['name'], 'unique'],
            [['full_name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'full_name' => 'Полное имя',
            'date_start' => 'ЧПД появления',
            'date_start_unix' => 'Unixtime появления',
            'sort' => 'Sort',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAltcoinWatchers()
    {
        return $this->hasMany(AltcoinWatcher::class, ['altcoin_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAltcoinLastData()
    {
        return $this->hasOne(AltcoinHistoryData::class, ['altcoin_id' => 'id'])->orderBy(['altcoin_date_id' => SORT_DESC]);
    }

    /**
     * Like load method but, it try to save date_start_unix and date_start
     *
     * @param array $data
     * @param null $formName
     * @return bool
     */
    public function loadWithDatesStart(array $data, $formName = null): bool
    {
        if ($this->load($data, $formName)) {
            if ($this->date_start) {
                $date = DateTime::createFromFormat('Y-m-d', $this->date_start);
                if ($date !== false) {
                    $this->date_start_unix = $date->getTimestamp();
                }
            }

            if ($this->date_start_unix) {
                $this->date_start = date('Y-m-d', $this->date_start_unix);
            }
            return true;
        }
        return false;
    }

    /**
     * Returns map id - name from all records from altcoin
     * Вернет карту id - name по всем записям из тбл altcoin
     *
     * @param bool $capitalize
     * @return array
     */
    public static function map(bool $capitalize = false): array
    {
        $altcoins = self::find()->select(['id', 'name'])->all();
        $map = ArrayHelper::map($altcoins, 'id', 'name');
        if ($capitalize) {
            return $map;
        }
        return array_map('strtolower', $map);
    }

    /**
     * @return array
     */
    public static function getAltcoinListId(): array
    {
        return self::find()->select(['id'])->column();
    }

    /**
     * @param bool $capitalize
     * @return array
     */
    public static function getAltcoinList(bool $capitalize = false): array
    {
        $altcoins = self::find()->select(['name'])->column();
        return $capitalize ? $altcoins : array_map('strtolower', $altcoins);
    }

    /**
     * @return array
     */
    public static function getCurrencyList(): array
    {
        return [self::RUB, self::USD, self::EUR];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function addNew(): array
    {
        $cryptoCompare = new CryptoCompare();
        $apiAnswer = $cryptoCompare->getPriceOnDate($this->name, $this->date_start_unix);
        if ($apiAnswer['success']) {
            $this->save(false);
            $dateId = AltcoinDate::find()->select(['id'])->where(['unix_date' => $this->date_start_unix])->scalar();
            AltcoinHistoryData::saveRow($this->id, $dateId, $apiAnswer['data'][$this->name]['USD']);
            return ['success' => true];
        }
        return [
            'success' => false,
            'error' => ArrayHelper::getValue($apiAnswer, 'data.Message')
        ];
    }
}