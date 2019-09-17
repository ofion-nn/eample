<?php

namespace api\controllers;

use Yii;
use common\models\City;
use common\models\CitySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CityController implements the CRUD actions for City model.
 */
class SiteController extends Controller {
    /**
     * Lists all City models.
     * @return mixed
     */
    public function actionIndex() {
        return "hello world";
    }

    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
}
