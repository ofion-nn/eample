<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Page */

$this->title = Yii::t('app','Добавление новой страницы');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Страницы'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-default col-md-12">

    <?= $this->render('_form', [
        'model' => $model,
        'langModels' => $langModels,
    ]) ?>

</div>
