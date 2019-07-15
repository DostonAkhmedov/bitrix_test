<?php

function CleanUpImages() {

    define("NO_KEEP_STATISTIC", true);
    define("NOT_CHECK_PERMISSIONS", true);

    // Директория, в которой лежат картинки товаров
    $dirPath = $_SERVER['DOCUMENT_ROOT'] . "/upload/iblock";

    // Получить файлы из таблицы "b_file", которые связаны с элементами инфоблоков
    $dbFile = \Bitrix\Main\FileTable::getList([
        'filter' => [
            '=MODULE_ID' => "iblock"
        ],
        'select' => [
            'SUBDIR',
            'FILE_NAME'
        ]
    ]);
    $arFiles = [];
    while ($arFile = $dbFile->fetch()) {
        $arFiles[] = $arFile["FILE_NAME"];
    }

    $rootDir = opendir($dirPath);

    while(($subDirName = readdir($rootDir)) !== false) {

        if ($subDirName == '.' || $subDirName == '..') {
            continue;
        }

        //Путь до подкатегорий с файлами
        $subDirPath = "$dirPath/$subDirName";

        //Счётчик файдов в директории
        $filesCount = 0;

        $rootSubDir = opendir($subDirPath);

        while (($fileName = readdir($rootSubDir)) !== false) {

            if ($fileName == '.' || $fileName == '..') {
                continue;
            }

            if (in_array($fileName, $arFiles)) {
                $filesCount++;
                continue;
            }

            //Полный путь до файла
            $fullPath = "$subDirPath/$fileName";

            //Удаление файла
            unlink($fullPath);

        }
        closedir($rootSubDir);

        //Удалить подкатегорию, если она пуста
        if (!$filesCount) {
            rmdir($subDirPath);
        }
    }
    closedir($rootDir);

    return __METHOD__."();";
}
