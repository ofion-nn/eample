<?php

namespace api\modules\v1\controllers;

use common\models\ArrayOfPlots;
use common\models\CallPartner;
use common\models\CallRequests;
use common\models\City;
use common\models\Concept;
use common\models\ContactForm;
use common\models\LandPlots;
use common\models\ReserveAnArray;
use common\models\ReservePlot;
use common\modules\page\models\Page;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

/**
 * Default controller for the `v1` module
 */
class DefaultController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        if (YII_ENV === 'dev') {
            $behaviors[] = [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Expose-Headers' => [],
                ],
            ];
        }

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'set-form' => ['POST'],
            ],
        ];
        return $behaviors;
    }


    public function beforeAction($action)
    {
        if ($action->id == 'set-form') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function init()
    {
        parent::init();
        Yii::$app->user->enableSession = false;    // no sessions for this controller
        Yii::$app->user->loginUrl = null;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // default this controller to JSON
    }

    public function actionAllArrays()
    {
        //Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $session = Yii::$app->session;
        $session->open();
        $response = [];
        $response['status'] = "success";
        $response['message'] = '';
        $cookies = Yii::$app->response->cookies;
        $session['csrf'] = Yii::$app->request->csrfToken;

// добавление новой куки в HTTP-ответ
        $cookies->add(new \yii\web\Cookie([
            'name' => 'csrf',
            'value' => Yii::$app->request->csrfToken,
        ]));
        $array_of_plots = ArrayOfPlots::find()->where(['active' => 1])->with('infrastructure')->with('concept')->with('land_plots')->all();
        $result_array = array();
        foreach ($array_of_plots as $key => $array) {
            $result_array[$key] = $array->toArray([], ['infrastructure', 'concept', 'land_plots', 'land_plots.concept']);
        }
        $response['data'] = $result_array;
        return $response;
    }

    public function actionAllConcepts()
    {
        //Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $response = [];
        $response['status'] = "success";
        $response['message'] = '';
        $concepts = Concept::find()->where(['status' => 1])->all();
        $response['data'] = $concepts;
        return $response;
    }

    public function actionAllCities()
    {
        //Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $response = [];
        $response['status'] = "success";
        $response['message'] = '';
        $cities = City::find()->where(['status' => 1])->all();
        $response['data'] = $cities;
        return $response;
    }

    public function actionAllPages()
    {
        $pages = Page::find()->indexBy('url_title')->with('lang')->asArray()->all();
        return [
            'status' => 'success',
            'message' => '',
            'data' => $pages
        ];
    }

    public function actionGetSettings()
    {
        $response = [];
        $response['status'] = "success";
        $response['message'] = '';
        $response['data'] = [];
        $config = Yii::$app->config->getGroup('contacts');
        if (!empty($config)) {
            foreach ($config as $item) {
                $response['data'][$item->key] = $item->value;
            }
        } else {
            $response['data'][] = '';
        }
        $response['data']['token'] = Yii::$app->request->csrfToken;

        return $response;
    }

    public function actionSetForm()
    {
        $request = Yii::$app->getRequest()->getBodyParams();

        $session = Yii::$app->session;
        $is_open = $session->isActive;
        $response = [];
        $response['status'] = "error";
        $response['message'] = '';
        /*  $data = $request['data'];*/
        $data = json_decode($request['data']);

        $tested = Yii::$app->request->validateCsrfToken($data->csrf);

        if ($is_open && $tested) {
            $subject = $data->form;

            if (!empty($subject)) {
                switch ($subject) {
                    case 'form-array':
                        if (!empty($data->name) && !empty($data->phone) && !empty($data->array_id)) {
                            $array_id = (int)$data->array_id;
                            $name = htmlspecialchars(trim($data->name));
                            $telephone = htmlspecialchars(trim($data->phone));
                            $model = new ReserveAnArray();
                            $model->name = $name;
                            $model->telephone = $telephone;
                            $model->array_of_plots_id = $array_id;
                            $model->status = 0;
                            $model->created_at = $model->updated_at = time();

                            if ($model->save()) {
                                $host = 'http://admin.' . $_SERVER['HTTP_HOST'] . '/reserve-an-array/update?id=' . $model->id;
                                $time = Yii::$app->formatter->asDatetime($model->updated_at);
                                $array_name = ArrayOfPlots::find()->select(['name'])->where(['id' => $model->array_of_plots_id])->one();

                                $body = 'Пользователь ' . $name . ' сделал заказ ' . $time . ' <br>на покупку массива:   ' . $array_name->name . '. <br> Подробнее о заказе можно узнать на сайте:  <a href="' . $host . '" >' . $host . '</a><br> или связаться с клиентом по телефону:  ' . $telephone;
                                if ($result = $this->_sendMessage('Заказ Массива', $body)) {
                                    $response['message'] = 'ok';
                                } else {
                                    $response['message'] = 'error';
                                }

                                $response['status'] = 'success';
                                $response['data'] = 'ok';
                            } else {
                                $response['message'] = 'save error: ' . $model->errors;
                            }
                        }
                        break;
                    case 'form-land-plot':
                        if (!empty($data->name) && !empty($data->phone)
                            && !empty($data->land_plot_id) /*&& !empty($request['data']['total_amount'])*/) {
                            $land_plot_id = (int)$data->land_plot_id;
                            $name = htmlspecialchars(trim($data->name));
                            $telephone = htmlspecialchars(trim($data->phone));
                            $model = new ReservePlot();
                            $model->name = $name;
                            $model->telephone = $telephone;
                            $model->land_plot_id = $land_plot_id;
                            $model->status = 0;
                            $model->created_at = $model->updated_at = time();
                            if ($model->save()) {
                                $host = 'http://admin' . $_SERVER['HTTP_HOST'] . '/reserve-plot/update?id=' . $model->id;
                                $time = Yii::$app->formatter->asDatetime($model->updated_at);
                                $l_plot = LandPlots::find()->select(['name', 'adress'])->where(['id' => $model->land_plot_id])->one();
                                $land_plot_name = !empty($l_plot->name) ? $l_plot->name : $l_plot->adress;

                                $body = 'Пользователь ' . $name . ' сделал заказ ' . $time . ' <br> на покупку участка:   ' . $land_plot_name . '. <br>Подробнее о заказе можно узнать на сайте: <a href="' . $host . '" >' . $host . '</a> <br> или связаться с клиентом по телефону:  ' . $telephone;
                                if ($result = $this->_sendMessage('Заказ участка', $body)) {
                                    $response['message'] = 'ok';
                                } else {
                                    $response['message'] = 'error';
                                }

                                $response['status'] = 'success';
                                $response['data'] = 'ok';
                            } else {
                                $response['message'] = 'save error: ' . $model->errors;
                            }
                        }
                        break;
                    case 'form-callback':
                        if (!empty($data->phone)) {
                            $telephone = htmlspecialchars(trim($data->phone));
                            $name = '';
                            if (isset($data->name) && !empty($data->name)) {
                                $name = htmlspecialchars(trim($data->name));
                            }
                            $model = new CallRequests();
                            $model->name = $name;
                            $model->telephone = $telephone;
                            $model->status = 0;
                            $model->created_at = $model->updated_at = time();
                            if ($model->save()) {

                                $host = 'http://admin.' . $_SERVER['HTTP_HOST'] . '/call-requests/update?id=' . $model->id;
                                $time = Yii::$app->formatter->asDatetime($model->updated_at);
                                $name = !empty($name) ? $name : '';

                                $body = 'Пользователь ' . $name . ' сделал заказ обратного звонка ' . $time . '. <br>Подробнее о заказе можно узнать на сайте: <a href="' . $host . '" >' . $host . ' </a> <br> или связаться с клиентом по телефону:  ' . $telephone;

                                if ($result = $this->_sendMessage('Заказ обратного звонка', $body)) {
                                    $response['message'] = 'ok';
                                } else {
                                    $response['message'] = 'error';
                                }

                                $response['status'] = 'success';
                                $response['data'] = 'ok';
                            } else {
                                $response['message'] = 'save error: ' . $model->errors;
                            }
                        }
                        break;
                    case 'form-partner':
                        $model = new CallPartner();
                        $model->name = htmlspecialchars($data->name);
                        $model->phone = htmlspecialchars($data->phone);
                        if ($model->save()) {
                            $host = 'http://admin.' . $_SERVER['HTTP_HOST'] . "/call-partner/update?id=$model->id";
                            $time = Yii::$app->formatter->asDatetime($model->created_at);

                            $body = 'Пользователь ' . $data->name . ' отправил заявку на партнёрство ' . $time . '. <br>Подробнее можно узнать на сайте: <a href="' . $host . '" >' . $host . ' </a> <br> или связаться с клиентом по телефону:  ' . $data->phone;

                            if ($result = $this->_sendMessage('Заказ обратного звонка', $body)) {
                                $message = 'ok';
                            } else {
                                $message = 'send mail error';
                            }
                            return [
                                'status' => 'success',
                                'message' => $message
                            ];
                        }
                        return [
                            'status' => 'error',
                            'message' => $model->errors
                        ];
                        break;
                }
            }
        }
        return $response;
    }

    private function _sendMessage($subject, $body)
    {
        $message = new ContactForm();
        $message->name = $subject;
        $message->body = $body;
        $message->email = Yii::$app->config->g('admin_email');
        if ($message->validate()) {
            return $message->sendEmail(Yii::$app->config->g('admin_email'));
        } else {
            return $message->errors;
        }
    }

}
