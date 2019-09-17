<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "array_of_plots".
 *
 * @property int $id
 * @property string $name
 * @property int $city_id
 * @property double $area
 * @property double $price_total
 * @property double $price_100
 * @property string $description
 * @property string $legal_system
 * @property string $coords
 * @property int $status
 * @property int $active
 * @property int $confirmed_rights
 * @property int $created_at
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property ArrayConcepts[] $arrayConcepts
 * @property ArrayInfrastructure[] $arrayInfrastructures
 * @property City $city
 * @property User $updatedBy
 * @property ReserveAnArray[] $reserveAnArrays
 */
class ArrayOfPlots extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'array_of_plots';
    }
    public $infrastructures;
    public $concepts;
    public $main_img;
    public $slider_img;
    public $map_img;
    public $panorama_img;


    public function behaviors()
    {
        return [
            [
                'class' => \mix8872\filesAttacher\behaviors\FileAttachBehavior::class,
                'tags' => ['image', 'slider', 'map_img', 'panorama_img'],
                'deleteOld' => ['image', 'map_img', 'panorama_img']
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['city_id', 'name', 'price_total', 'price_100', 'area', 'coords'], 'required'],
            [['city_id', 'status', 'active', 'created_at', 'updated_at', 'updated_by'], 'integer'],
            [['area', 'price_total', 'price_100'], 'number'],
            [['description'], 'string'],
            [['infrastructures', 'concepts'], 'each', 'rule' => ['integer']],
            [['name', 'confirmed_rights', 'video', 'panorama_url', 'legal_system', 'coords'], 'string', 'max' => 255],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'id']],
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
            'city_id' => Yii::t('app', 'Город'),
            'area' => Yii::t('app', 'Площадь'),
            'confirmed_rights' => Yii::t('app', 'Подтвержденное право'),
            'video' => Yii::t('app', 'id видео YouTube'),
            'panorama_url' => Yii::t('app', 'Url для панорамы'),
            'price_total' => Yii::t('app', 'Общая стоимость'),
            'price_100' => Yii::t('app', 'Стоимость за сотку'),
            'description' => Yii::t('app', 'Описание'),
            'legal_system' => Yii::t('app', 'Собственник участка'),
            'coords' => Yii::t('app', 'Координаты на карте'),
            'status' => Yii::t('app', 'Статус'),
            'active' => Yii::t('app', 'Активен'),
            'created_at' => Yii::t('app', 'Дата создания'),
            'updated_at' => Yii::t('app', 'Дата обновления'),
            'updated_by' => Yii::t('app', 'Изменено пользователем'),
            'infrastructures' => Yii::t('app', 'Инфраструктура'),
            'concepts' => Yii::t('app', 'Концепции'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $old_infrastructures = \common\models\ArrayInfrastructure::find()->select(['infrastructure_id'])->where(['array_id' => $this->id, 'status' => 1])->asArray()->indexBy('infrastructure_id')->all();
            $arr_old_infrastructure = ArrayHelper::getColumn($old_infrastructures, 'infrastructure_id');
            if(empty($old_infrastructures) && is_array($this->infrastructures) && !empty($this->infrastructures)){
                foreach ($this->infrastructures as $key => $val){
                    $arrInfrastructure = new ArrayInfrastructure();
                    $arrInfrastructure->array_id = $this->id;
                    $arrInfrastructure->infrastructure_id = $val;
                    $arrInfrastructure->status = 1;
                    $arrInfrastructure->created_at = $arrInfrastructure->updated_at = time();
                    $arrInfrastructure->updated_by = Yii::$app->user->id;
                    $arrInfrastructure->save();
                }
            }
            elseif (empty($this->infrastructures) && !empty($arr_old_infrastructure)){
                foreach ($arr_old_infrastructure as $key => $val){
                    $res =  ArrayInfrastructure::find()->where(['infrastructure_id' => $val, 'status' => 1, 'array_id' => $this->id])->one();
                    $res->delete();
                }
            }
            if(!empty($arr_old_infrastructure) && is_array($this->infrastructures) && !empty($this->infrastructures)){
                $diff_old_infrastructure = array_diff($arr_old_infrastructure, $this->infrastructures); // получаем массив элементов, которые надо удалить
                $diff_new_infrastructure = array_diff($this->infrastructures, $arr_old_infrastructure); // получаем массив элементов, которые надо добавить

                if(!empty($diff_old_infrastructure)){
                    foreach ($diff_old_infrastructure as $key => $val){
                        $res =  ArrayInfrastructure::find()->where(['infrastructure_id' => $val, 'status' => 1, 'array_id' => $this->id])->one();
                        $res->delete();
                    }
                }
                if(!empty($diff_new_infrastructure)){
                    foreach ($diff_new_infrastructure as $key => $val){
                        $arrInfrastructure = new ArrayInfrastructure();
                        $arrInfrastructure->array_id = $this->id;
                        $arrInfrastructure->infrastructure_id = $val;
                        $arrInfrastructure->status = 1;
                        $arrInfrastructure->created_at = $arrInfrastructure->updated_at = time();
                        $arrInfrastructure->updated_by = Yii::$app->user->id;
                        $arrInfrastructure->save();
                    }
                }
            }
            $old_concepts = \common\models\ArrayConcepts::find()->select(['concept_id'])->where(['array_of_plots_id' => $this->id, 'status' => 1])->asArray()->indexBy('concept_id')->all();
            $arr_old_concepts = ArrayHelper::getColumn($old_concepts, 'concept_id');
            if(empty($old_concepts) && is_array($this->concepts) && !empty($this->concepts)){
                foreach ($this->concepts as $key => $val){
                    $arrConcepts = new ArrayConcepts();
                    $arrConcepts->array_of_plots_id = $this->id;
                    $arrConcepts->concept_id = $val;
                    $arrConcepts->status = 1;
                    $arrConcepts->created_at = $arrConcepts->updated_at = time();
                    $arrConcepts->updated_by = Yii::$app->user->id;
                    $arrConcepts->save();
                }
            }
            elseif (empty($this->concepts) && !empty($arr_old_concepts)){
                foreach ($arr_old_concepts as $key => $val){
                    $res = ArrayConcepts::find()->where(['concept_id' => $val, 'status' => 1, 'array_of_plots_id' => $this->id])->one();
                    $res->delete();
                }
            }
            if(!empty($arr_old_concepts) && is_array($this->concepts) && !empty($this->concepts)){
                $diff_old_concepts = array_diff($arr_old_concepts, $this->concepts); // получаем массив элементов, которые надо удалить
                $diff_new_concepts = array_diff($this->concepts, $arr_old_concepts); // получаем массив элементов, которые надо добавить
                if(!empty($diff_old_concepts)){
                    foreach ($diff_old_concepts as $key => $val){
                        $res = ArrayConcepts::find()->where(['concept_id' => $val, 'status' => 1, 'array_of_plots_id' => $this->id])->one();
                        $res->delete();
                    }
                }
                if(!empty($diff_new_concepts)){
                    foreach ($diff_new_concepts as $key => $val){
                        $arrConcepts = new ArrayConcepts();
                        $arrConcepts->array_of_plots_id = $this->id;
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

    public function afterFind()
    {
        parent::afterFind();

        $this->slider_img = $this->getFiles('slider');
        $this->main_img = $this->getFiles('image');
        $this->map_img = $this->getFiles('map_img');
        $this->panorama_img = $this->getFiles('panorama_img');
        $infrastructures = ArrayInfrastructure::find()->select(['infrastructure_id'])->where(['array_id' => $this->id, 'status' => '1'])->indexBy('infrastructure_id')->asArray()->all();
        $this->infrastructures = ArrayHelper::getColumn($infrastructures, 'infrastructure_id');
        $concepts = ArrayConcepts::find()->select(['concept_id'])->where(['array_of_plots_id' => $this->id, 'status' => '1'])->indexBy('concept_id')->asArray()->all();
        $this->concepts = ArrayHelper::getColumn($concepts, 'concept_id');
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArrayConcepts()
    {
        return $this->hasMany(ArrayConcepts::class, ['array_of_plots_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArrayInfrastructures()
    {
        return $this->hasMany(ArrayInfrastructure::class, ['array_id' => 'id'])->where(['status' => 1]);
    }

    public function getInfrastructure()
    {
        return $this->hasMany(Infrastructure::class, ['id' => 'infrastructure_id'])->via('arrayInfrastructures');
    }

    public function getConcept()
    {
        return $this->hasMany(Concept::class, ['id' => 'concept_id'])->via('arrayConcepts');
    }

    public function getLand_plots()
    {
        return $this->hasMany(LandPlots::class, ['array_id' => 'id'])->where(['active' => 1])->with('concept');
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
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
    public function getReserveAnArrays()
    {
        return $this->hasMany(ReserveAnArray::class, ['array_of_plots_id' => 'id']);
    }

    public function fields()
    {
        return array_merge(parent::fields(),[
            'main_img', 'slider_img', 'map_img', 'panorama_img'
        ]);
    }
}
