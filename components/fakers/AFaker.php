<?php

namespace app\components\fakers;

use yii\db\ActiveRecord;

abstract class AFaker
{
    /** @var AFaker */
    protected $faker;

    /** @var int  */
    protected int $unixTimeFrom = 315532800; // 01.01.1980

    /** @var int  */
    protected int $unixTimeTo; // current unix time

    public function __construct()
    {
        $this->unixTimeTo = time();
    }

    abstract function create();

    abstract function createAsArray();
}