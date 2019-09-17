<?php

use yii\db\Migration;

/**
 * Handles the creation of table `page`.
 */
class m180518_090104_create_page_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('page', [
            'id' => $this->primaryKey(),
            'url_title' => $this->string()->notNull(),
            'template' => $this->string(),
            'status' => $this->boolean(),
        ]);

        $this->createTable('lang_page', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'content' => $this->text(),
            'lang' => $this->string(30),
            'page_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `page_id`
        $this->createIndex(
            'idx-lang_page-page_id',
            'lang_page',
            'page_id'
        );

        // add foreign key for table `page`
        $this->addForeignKey(
            'fk-lang_page-page_id',
            'lang_page',
            'page_id',
            'page',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-lang_page-page_id',
            'lang_page'
        );

        // drops index for column `page_id`
        $this->dropIndex(
            'idx-lang_page-page_id',
            'lang_page'
        );

        $this->dropTable('lang_page');
        $this->dropTable('page');
    }
}
