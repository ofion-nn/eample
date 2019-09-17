<?php

namespace common\modules\page\models;

use Yii;

/**
 * This is the model class for table "lang_page".
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $lang
 * @property int $page_id
 *
 * @property Page $page
 */
class LangPage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lang_page';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'page_id'], 'required'],
            [['content'], 'string'],
            [['page_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['lang'], 'string', 'max' => 30],
            [['page_id'], 'exist', 'skipOnError' => true, 'targetClass' => Page::class, 'targetAttribute' => ['page_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Заголовок'),
            'content' => Yii::t('app', 'Контент'),
            'lang' => Yii::t('app', 'Язык'),
            'page_id' => Yii::t('app', 'Код страницы'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(Page::class, ['id' => 'page_id']);
    }
}
