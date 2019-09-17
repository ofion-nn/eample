<?php

use common\modules\page\assets\PageAsset;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mihaildev\ckeditor\CKEditor;
use mihaildev\elfinder\ElFinder;

if ($model->isNewRecord){
    PageAsset::register($this);
}

/* @var $this yii\web\View */
/* @var $model common\models\Page */
/* @var $form yii\widgets\ActiveForm */
?>


<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="panel-heading">
        <div class="form-group pull-left">
            <?= Html::tag('h3', $this->title) ?>
        </div>
        <div class="form-group pull-right">
            <?= Html::a(Yii::t('app', 'Назад'), ['/page'], ['class' => 'btn btn-warning']) ?>
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Добавить') : Yii::t('app', 'Обновить'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">

                <?php if ($model->isNewRecord): ?>
                    <?= $form->field($model, 'url_title')->textInput(['maxlength' => true]) ?>
                <?php endif; ?>

                <?= $form->field($model, 'status', [])->checkbox(['checked ' => '']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'template')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="col-lg-12">
            <ul class="nav nav-tabs tabs tabs-top">
                <?php foreach ($langModels as $key => $lang): ?>
                    <li class="active tab">
                        <a href="#<?= $key ?>" data-toggle="tab" aria-expanded="false">
                            <span class="visible-xs"><i class="fa fa-home"></i></span>
                            <span class="hidden-xs"><?= $key ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content">
                <?php foreach ($langModels as $key => $lang): ?>
                    <div class="tab-pane active" id="<?= $key ?>">


                        <div class="row">
                            <div class="col-md-6"><?= $form->field($lang, '[' . $key . ']title')->textInput(['maxlength' => true]) ?></div>

                            <div class="col-md-12">
                                <?= $form->field($lang, '[' . $key . ']content')->widget(CKEditor::className(), [
                                    'editorOptions' => [
                                        'preset' => 'full', //разработанны стандартные настройки basic, standard, full данную возможность не обязательно использовать
                                        'inline' => false,
                                        'basicEntities' => false,
                                        'allowedContent' => true
                                    ],
                                ])
                                ?>
                            </div>
                        </div>

                        <?php if ($model->isNewRecord): ?>
                            <?= $form->field($lang, '[' . $key . ']lang')->hiddenInput(['value' => $key])->label(false); ?>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>


    </div>

<?php ActiveForm::end(); ?>