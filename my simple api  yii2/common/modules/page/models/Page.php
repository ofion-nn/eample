<?php

namespace common\modules\page\models;

use Yii;

/**
 * This is the model class for table "page".
 *
 * @property int $id
 * @property string $url_title
 * @property string $template
 * @property int $status
 *
 * @property LangPage[] $langPages
 */
class Page extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'page';
    }

    const STATUS_ACTIVE   = '1';
    const STATUS_HIDE   = '0';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url_title', 'status'], 'required'],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_HIDE], 'message' => Yii::t('page','Incorrect status')],
            [['url_title', 'template'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'url_title' => Yii::t('app', 'Url заголовок'),
            'template' => Yii::t('app', 'Шаблон'),
            'status' => Yii::t('app','Активна'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLang()
    {
        return $this->hasMany(LangPage::class, ['page_id' => 'id'])->indexBy('lang');
    }

    public function getData(){
        $language = Yii::$app->language;
        $data_lang = $this->getLang()->where(['lang'=>$language])->one();
        return $data_lang;
    }

    public function getPages(){
        return $this->find()->all();
    }

}
