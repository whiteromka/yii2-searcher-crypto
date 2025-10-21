<?php

namespace app\models\api;

class User extends \app\models\User
{
    const SCENARIO_FULL = 'full';
    const SCENARIO_WITH_ESTATE = 'withEstate';
    const SCENARIO_WITH_PASSPORT = 'withPassport';

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $this->setScenario(self::SCENARIO_FULL);
        $fields = parent::fields();
        unset(
            $fields['password_hash'],
            $fields['access_token'],
            $fields['auth_key'],
        );

        switch ($this->scenario) {
            case self::SCENARIO_FULL:
                $fields['passport'] = fn() => $this->passport;
                $fields['estate'] = fn() => $this->estate;
                $fields['fullAge'] = fn() => $this->fullAge;
                break;

            case self::SCENARIO_WITH_ESTATE:
                $fields['estate'] = function() {
                    return $this->estate;
                };
                break;

            case self::SCENARIO_WITH_PASSPORT:
                $fields['passport'] = function() {
                    return $this->passport;
                };
                break;
        }
        return $fields;
    }

    /**
     * {@inheritdoc} GET http://yii2-strange2.local/api/user?expand=fullAge
     */
    public function extraFields()
    {
        return [
            'fullAge',
        ];
    }
}