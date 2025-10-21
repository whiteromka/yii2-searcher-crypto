<?php

namespace app\controllers\api;

use Yii;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;
use app\models\User;

class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        return $behaviors;
    }

    /**
     * Возвращает BearerToken по переданному email, password
     *
     * @return array []
     * @throws UnauthorizedHttpException
     */
    public function actionLogin(): array
    {
        $email = Yii::$app->request->post('email');
        $password = Yii::$app->request->post('password');
        if (empty($email) || empty($password)) {
            throw new UnauthorizedHttpException('Данные email password не могут быть пустыми');
        }
        $user = User::find()->where(['email' => $email])->one();

        if ($user && $user->checkPassword($password)) {
            return [
                'success' => true,
                'token' => $user->getAuthTokenOrGenerate(),
            ];
        }
        throw new UnauthorizedHttpException('Данные email password не верны');
    }

    /**
     * Удаляет BearerToken по переданному BearerToken, и разавторизует
     *
     * @return array []
     * @throws UnauthorizedHttpException
     */
    public function actionLogout(): array
    {
        $user = Yii::$app->user->identity;
        if ($user) {
            $user->access_token = null;
            $user->save();
            Yii::$app->user->logout();

            return [
                'success' => true,
                'message' => 'Токен удален'
            ];
        }
        throw new UnauthorizedHttpException('Вы не авторизованы');
    }
}