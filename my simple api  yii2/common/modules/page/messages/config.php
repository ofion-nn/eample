<?php
   return [
    'sourcePath' => dirname(dirname(__DIR__)),
    'messagePath' => __DIR__,
    'languages' => ['ru-RU'],
    'translator' => 'Yii::t',
    'sort' => true,
    'overwrite' => false,
    'removeUnused' => false,
    'markUnused' => true,
    'except' => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '/BaseYii.php',
    ],
    'only' => ['*.php',],
    'format' => 'php'
];