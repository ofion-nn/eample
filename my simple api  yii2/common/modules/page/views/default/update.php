<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Page */

$this->title = Yii::t('app', 'Редактирование:  ') . $model->url_title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Страницы'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->url_title, 'url' => ['index',]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Изменить');
?>
<div class="panel panel-default col-md-12">
    <?= $this->render('_form', [
        'model' => $model,
        'langModels' => $langModels,
    ]) ?>
</div>
