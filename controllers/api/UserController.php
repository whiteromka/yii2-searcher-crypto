<?php

namespace app\controllers\api;

use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;

/**
 * GET .../api/user?page=10&per-page=100
 * GET .../api/user/1
 * POST .../api/user/create
 * PUT .../api/user/update
 * DELETE .../api/user/delete/1
 */
class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Разрешаем создание пользователя без аутентификации
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['index', 'view', 'create', 'update', 'delete', 'options'], // разрешаем без токена
        ];

        return $behaviors;
    }
}