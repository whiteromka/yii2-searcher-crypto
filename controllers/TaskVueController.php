<?php

namespace app\controllers;

use yii\web\Controller;

class TaskVueController extends Controller
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}