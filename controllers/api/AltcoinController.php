<?php

namespace app\controllers\api;

use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;

/**
 * GET .../api/altcoin?page=10&per-page=100
 * GET .../api/altcoin/1
 * POST .../api/altcoin/create
 * PUT .../api/altcoin/update
 * PATCH .../api/altcoin/update
 * DELETE .../api/altcoin/delete/1
 */
class AltcoinController extends ActiveController
{
    public $modelClass = 'app\models\api\Altcoin';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Временно разрешаем создание пользователя без аутентификации
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['index', 'view', 'create', 'update', 'delete', 'options'],
        ];

        return $behaviors;
    }
}