<?php

namespace app\models\api;

class Altcoin extends \app\models\Altcoin
{
    public function fields()
    {
        $fields = parent::fields();;
        unset(
            $fields['date_start_unix'],
            $fields['date_start'],
            $fields['sort'],
            $fields['created_at'],
            $fields['updated_at'],
        );
        $fields['price'] = fn() => $this->altcoinLastData->price;
        $fields['date'] = fn() => $this->altcoinLastData->altcoinDate->date;
        return $fields;
    }
}