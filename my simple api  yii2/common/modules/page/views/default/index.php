<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Страницы');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel col-md-12">
    <div class="panel-heading">
        <h2 class="pull-left"><?= Yii::t('app', 'Страницы') ?></h2>
        <div class="panel-heading__btn-block">
            <?= Html::a(Yii::t('app', 'Добавить страницу'), ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>
    <div class="panel-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'label' => Yii::t('app', 'Название страницы'),
                    'value' => function ($model) {
                        return $model->data->title;
                    }
                ],
                'url_title',
                'template',
                [
                    'attribute' => 'status',
                    'value' => function ($model) {
                        return $model->status ? 'Да' : 'Нет';
                    }
                ],
                ['class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                ],
            ],
        ]); ?>
    </div>
</div>
