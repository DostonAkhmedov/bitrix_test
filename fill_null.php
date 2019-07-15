<?php

// Имя читаемого файла
$fileName = "chart.json";

// Данные из файла
$fileContent = @file_get_contents($fileName);

// Преобразование в массив
$chartList = @json_decode($fileContent, true);

// Счетчик количество значение 100 для каждого столбца
$cnt_100 = [];

for ($i = 0; $i < count($chartList); $i++) {
    for ($j = 1; $j < count($chartList[$i]); $j++) { // Начинаем с 1-ого элемента, 0-ое значение пропустим (time)
        if ($chartList[$i][$j] == 100) { // Посчитаем длины последовательности 100
            $cnt_100[$j]++;
        } else { // Если отличное от 100
            $cnt_100[$j] = 0;
        }
        // Если длина последовательности 100 больше 3, то меняем значение на null
        if ($cnt_100[$j] == 4) {
            for ($k = 0; $k <= 3; $k++) {
                $chartList[$i - $k][$j] = null;
            }
        } elseif ($cnt_100[$j] > 4) {
            $chartList[$i][$j] = null;
        }
    }
}

// Имя файла для вывода
$resultFileName = "chart_result.json";

// Написать данные в файл
file_put_contents($resultFileName, json_encode($chartList));