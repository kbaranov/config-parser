<?php

// Исходный контент из файла
$source = <<< FILE
db.user.name = username
db.user.password = pswd1234
db.type = postgresql
php.version = 9.2
db.user.login &= db.user.name
db.user.name = patric
FILE;

// Результат конвертации (для теста)
$resultValid = [
    "db" => [
        "user" => [
            "name" => "patric",
            "password" => "pswd1234",
            "login" => "patric",
        ],
        "type" => "postgresql",
    ],
    "php" => [
        "version" => "9.2",
    ]
];

/**
 * Конвертер
 */
function converter($strIn) {
    $lines = explode("\n", $strIn); //альтернативный вариант - читать файл построчно

    //в этом циклическом блоке учитываются возможные ссылки на другие переменные
    //(вместо прямого указания значений)
    $arrTmp = [];
    foreach ($lines as $line) {
        $line = trim($line);
        preg_match('/([^ ]*) ?(\&?=) ?([^ ]*)/', $line, $matches);
        if ($matches[2] == '&=') {
            $arrTmp[$matches[1]] = &$arrTmp[$matches[3]];
        } else {
            $arrTmp[$matches[1]] = $matches[3];
        }
    }

    //в этом циклическом блоке предыдущий временный массив трансформируется в результат
    //(составные ключи временного массива "разделяются" для создания вложенного массива)
    $arrOut = [];
    foreach ($arrTmp as $key => $value) {
        $nestedKeys = explode('.', $key);
        $arrRef = &$arrOut;
        for ($i = 0; $i < (count($nestedKeys) - 1); $i++) {
            $arrRef = &$arrRef[$nestedKeys[$i]];
        }
        $arrRef[$nestedKeys[$i]] = $value;
    }

    return $arrOut;
}

/**
 * Тест
 */
function test($value1, $value2) {
    return $value1 == $value2;
}

$result = converter($source);

var_dump(test($result, $resultValid));
