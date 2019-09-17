<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "land_plots".
 *
 * @property int $id
 * @property string $name
 * @property string $adress
 * @property string $price_total
 * @property string $price_100
 * @property double $area
 * @property int $concept_id
 * @property int $status
 * @property int $active
 * @property string $legal_system
 * @property int $array_id
 * @property string $cadastral_number
 * @property int $created_at
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property LandPlotConcept[] $landPlotConcepts
 * @property ArrayOfPlots $array
 * @property Concept $concept
 * @property User $updatedBy
 * @property ReservePlot[] $reservePlots
 */
class LandPlots extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'land_plots';
    }

    public $concepts;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price_total', 'price_100', 'area'], 'number'],
            [['status', 'active', 'array_id', 'created_at', 'updated_at', 'updated_by'], 'integer'],
            [['name', 'adress', 'legal_system', 'cadastral_number'], 'string', 'max' => 255],
            [['concepts'], 'each', 'rule' => ['integer']],
            [['array_id'], 'exist', 'skipOnError' => true, 'targetClass' => ArrayOfPlots::class, 'targetAttribute' => ['array_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Название'),
            'adress' => Yii::t('app', 'Адрес'),
            'price_total' => Yii::t('app', 'Общая стоимость (руб)'),
            'price_100' => Yii::t('app', 'Стоимость за сотку (руб)'),
            'area' => Yii::t('app', 'Площадь (сотка)'),
            'status' => Yii::t('app', 'Статус'),
            'active' => Yii::t('app', 'Активен'),
            'legal_system' => Yii::t('app', 'Правовая форма'),
            'array_id' => Yii::t('app', 'Массив'),
            'cadastral_number' => Yii::t('app', 'Кадастровый номер'),
            'created_at' => Yii::t('app', 'Дата создания'),
            'updated_at' => Yii::t('app', 'Дата изменения'),
            'updated_by' => Yii::t('app', 'Изменено пользователем'),
            'concepts' => Yii::t('app', 'Концепции'),
        ];
    }


    public function afterFind()
    {
        parent::afterFind();

        $concepts = LandPlotConcept::find()->select(['concept_id'])->where(['land_plot_id' => $this->id, 'status' => '1'])->indexBy('concept_id')->asArray()->all();
        $this->concepts = ArrayHelper::getColumn($concepts, 'concept_id');
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $old_concepts = \common\models\LandPlotConcept::find()->select(['concept_id'])->where(['land_plot_id' => $this->id, 'status' => 1])->asArray()->indexBy('concept_id')->all();
            $arr_old_concepts = ArrayHelper::getColumn($old_concepts, 'concept_id');
            if (empty($old_concepts) && is_array($this->concepts) && !empty($this->concepts)) {
                foreach ($this->concepts as $key => $val) {
                    $arrConcepts = new LandPlotConcept();
                    $arrConcepts->land_plot_id = $this->id;
                    $arrConcepts->concept_id = $val;
                    $arrConcepts->status = 1;
                    $arrConcepts->created_at = $arrConcepts->updated_at = time();
                    $arrConcepts->updated_by = Yii::$app->user->id;
                    $arrConcepts->save();
                }
            } elseif (empty($this->concepts) && !empty($arr_old_concepts)) {
                foreach ($arr_old_concepts as $key => $val) {
                    $res = LandPlotConcept::find()->where(['concept_id' => $val, 'status' => 1, 'land_plot_id' => $this->id])->one();
                    $res->delete();
                }
            }
            if (!empty($arr_old_concepts) && is_array($this->concepts) && !empty($this->concepts)) {
                $diff_old_concepts = array_diff($arr_old_concepts, $this->concepts); // получаем массив элементов, которые надо удалить
                $diff_new_concepts = array_diff($this->concepts, $arr_old_concepts); // получаем массив элементов, которые надо добавить
                if (!empty($diff_old_concepts)) {
                    foreach ($diff_old_concepts as $key => $val) {
                        $res = LandPlotConcept::find()->where(['concept_id' => $val, 'status' => 1, 'land_plot_id' => $this->id])->one();
                        $res->delete();
                    }
                }
                if (!empty($diff_new_concepts)) {
                    foreach ($diff_new_concepts as $key => $val) {
                        $arrConcepts = new LandPlotConcept();
                        $arrConcepts->land_plot_id = $this->id;
                        $arrConcepts->concept_id = $val;
                        $arrConcepts->status = 1;
                        $arrConcepts->created_at = $arrConcepts->updated_at = time();
                        $arrConcepts->updated_by = Yii::$app->user->id;
                        $arrConcepts->save();
                    }
                }
            }
            //
            return true;
        }
        return false;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLandPlotConcepts()
    {
        return $this->hasMany(LandPlotConcept::class, ['land_plot_id' => 'id']);
    }


    public function getConcept()
    {
        return $this->hasMany(Concept::class, ['id' => 'concept_id'])->via('landPlotConcepts');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArray()
    {
        return $this->hasOne(ArrayOfPlots::class, ['id' => 'array_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReservePlots()
    {
        return $this->hasMany(ReservePlot::class, ['land_plot_id' => 'id']);
    }
}
