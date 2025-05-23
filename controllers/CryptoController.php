<?php

namespace app\controllers;

use app\components\api\CryptoCompare;
use app\models\Altcoin;
use app\models\AltcoinHistoryData;
use app\models\AltcoinWatcher;
use app\models\CryptoRequestForm;
use app\models\AltcoinHistory;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;
use Yii;

class CryptoController extends Controller
{
    /**
     * @return string
     * @throws \Exception
     */
    public function actionAddAltcoin(): string
    {
        // ToDo дописать сбор данных по началу парсинга крипты
        $altcoin = new Altcoin();
        $altcoins = Altcoin::find()->with(['altcoinWatchers'])->all();

        if ($altcoin->loadWithDatesStart(Yii::$app->request->post())) {
            $result = $altcoin->addNew();
            $session = Yii::$app->session;
            if ($result['success']) {
                $session->setFlash('success', 'Альткойн ' . $altcoin->name . ' добавлен');
            } else {
                $session->setFlash('danger', 'Ошибка. Альткойн ' . $altcoin->name . ' не был добавлен.' . $result['error']);
            }
        }
        return $this->render('add-altcoin', [
            'altcoin' => new Altcoin(),
            'altcoins' => $altcoins,
            'altcoinWatcher' => new AltcoinWatcher()
        ]);
    }

    /**
     * Adding watcher
     *
     * @return Response
     */
    public function actionAddWatcher()
    {
        $watcher = new AltcoinWatcher();
        if ($watcher->load(Yii::$app->request->post())) {
            $watcher->calculateExpectation();
            if (!$watcher->save()) {
                Yii::$app->session->setFlash('danger', current($watcher->firstErrors));
            }
        }
        return $this->redirect(['add-altcoin']);
    }

    /**
     * @return Response
     */
    public function actionDeleteWatcher()
    {
        $id = Yii::$app->request->get('id');
        AltcoinWatcher::deleteAll(['id' => $id]);
        return $this->asJson(['success' => true]);
    }

    /**
     * @return string
     */
    public function actionRates(): string
    {
        $app = Yii::$app;
        $cryptoForm = new CryptoRequestForm();
        $prices = ['success' => false];
        if ($cryptoForm->load($app->request->get())) {
            $prices = (new CryptoCompare())->getMultiPrice($cryptoForm->altcoinList, $cryptoForm->currencyList);
            if ($prices['error']) {
                $app->session->setFlash('danger', $prices['error']);
            }
        }
        return $this->render('rates', ['cryptoRequestForm' => $cryptoForm, 'prices' => $prices]);
    }

    /**
     * @return Response
     */
    public function actionGetRates()
    {
        $data = (new CryptoCompare())->getMultiPrice(Altcoin::getAltcoinList(true), ['USD']);
        return $this->asJson($data);
    }

    /**
     * @return string
     */
    public function actionCharts(): string
    {
        $altcoins = Altcoin::map();
        $prices = (new AltcoinHistoryData())->getLastPrices();
        return $this->render('charts', [
            'altcoins' => $altcoins,
            'prices' => $prices
        ]);
    }

    /**
     * @param string $altcoin
     *
     * @return Response
     * @throws Exception
     */
    public function actionGetDataCharts(string $altcoin): Response
    {
        $data = (new AltcoinHistoryData())->getDataCharts($altcoin);
        return $this->asJson([
            'success' => true,
            'data' => $data
        ]);
    }
}