<?php

namespace app\commands;

use Exception;
use Generator;
use yii\console\Controller;
use Yii;


/**
 * Class TController
 * @package app\commands
 */
class TController extends Controller
{
    /**
     * php yii t/index
     * @throws Exception
     */
    public function actionIndex()
    {
        $file = Yii::getAlias('@app/web/t.txt');
        foreach ($this->readBigFile($file) as $row) {
            echo $row;
        }
    }

    /**
     * @throws Exception
     */
    public function readBigFile($file): Generator
    {
        $file = fopen($file, 'r');
        if (!$file) {
            throw new Exception('Файл не найден!');
        }

        try {
            while (($line = fgets($file)) !== false) {
                yield $line;
            }
        } finally {
            fclose($file);
        }
    }
}