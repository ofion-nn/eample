<?php

namespace common\modules\page\controllers;

use common\modules\page\models\LangPage;
use Yii;
use common\modules\page\models\Page;
use common\models\PageSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\base\Model;

/**
 * DefaultController implements the CRUD actions for Page model.
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Page models.
     * @return mixed
     */
    public function actionIndex()
    {
        $models = Page::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $models
        ]);
        /* error_log(print_r($dataProvider, 1));*/

        return $this->render('index', [
            'dataProvider' => $dataProvider,

        ]);
    }


    /**
     * Creates a new Page model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Page();
        $langs = Yii::$app->getModule('languages')->languages;
        $saved = true;

        $langModels = array();
        foreach ($langs as $lang) {
            $langModels[$lang] = new LangPage();
        }

        if ($model->load(Yii::$app->request->post()) && Model::loadMultiple($langModels, Yii::$app->request->post())) {
            if ($model->save()) {
                foreach ($langModels as $item) {
                    $item->page_id = $model->id;
                    if (!$item->save()) {
                        error_log(print_r($item->getErrorSummary(1), 1));
                        $saved = false;
                    }
                }
                if ($saved) {
                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Страница добавлена'));
                    return $this->redirect(['index',]);
                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'Ошибка добавления Страницы. Проверьте введенные данные!'));
                }
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'Ошибка добавления Страницы. Проверьте введенные данные!'));
                error_log(print_r($model->getErrorSummary(1), 1));
            }

        }

        return $this->render('create', [
            'model' => $model,
            'langModels' => $langModels,
        ]);
    }

    /**
     * Updates an existing Page model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $langModels = array();
        $saved = true;
        $langModels = LangPage::find()->where(['page_id' => $model->id])->indexBy('lang')->all();
        /*error_log(print_r($langModels, 1));*/

        if ($model->load(Yii::$app->request->post()) && Model::loadMultiple($langModels, Yii::$app->request->post())) {
            if ($model->save()) {
                foreach ($langModels as $item) {
                    if (!$item->save()) {
                        error_log(print_r($item->getErrorSummary(1), 1));
                        $saved = false;
                    }
                }
                if ($saved) {
                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Страница обновлена'));
                    return $this->redirect(['index',]);
                } else {
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'Ошибка обновления страницы. Проверьте введенные данные!'));
                }
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'Ошибка обновления страницы. Проверьте введенные данные!'));
                error_log(print_r($model->getErrorSummary(1), 1));
            }

        }

        return $this->render('update', [
            'model' => $model,
            'langModels' => $langModels,

        ]);
    }

    /**
     * Deletes an existing Page model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Page model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Page the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Page::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
