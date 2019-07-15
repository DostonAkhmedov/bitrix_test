<?php

namespace Sprint\Migration;

use \Bitrix\Main\Loader;
use Exception;

class Version20190714 extends Version {

    protected $description = "Добавить товар и его торговые предложения";

    function up()
    {
        // TODO: Implement up() method.

        $this->out("Начало ...");

        if (!Loader::includeModule("iblock")) {
            $this->outError("Не удалось запустить модуль [Информационные блоки]");
            return;
        }

        if (!Loader::includeModule("catalog")) {
            $this->outError("Не удалось запустить модуль [Каталог]");
            return;
        }

        $catalogOffersIblockId = 2; // Ид инфоблока предложений
        $productName = "Диван"; // Наименование товара
        $offerName = "Диван чёрный цвет"; // Наименование торгового предложения
        $offerPrice = 45000; // Цена торгового предложения

        $arCatalog = \Bitrix\Catalog\CatalogIblockTable::getRow([
            'filter' => [
                '=IBLOCK_ID' => $catalogOffersIblockId
            ],
            'select' => ['ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID']
        ]);

        $iblockCatalogId = $arCatalog["PRODUCT_IBLOCK_ID"]; // Ид инфоблока товаров
        $skuPropertyId = $arCatalog["SKU_PROPERTY_ID"]; // ID свойства в инфоблоке предложений типа "Привязка к товарам (SKU)"

        $arFields = [
            'NAME' => $productName,
            'IBLOCK_ID' => $iblockCatalogId,
            'ACTIVE' => 'Y'
        ];

        $obElement = new \CIBlockElement();

        $productId = $obElement->Add($arFields); // Добавить товар

        if ($productId) {

            $obElement = new \CIBlockElement();

            // Свойство торгового предложения
            $arOfferProps = [
                $skuPropertyId => $productId
            ];
            $arOfferFields = [
                'NAME' => $offerName,
                'IBLOCK_ID' => $catalogOffersIblockId,
                'ACTIVE' => 'Y',
                'PROPERTY_VALUES' => $arOfferProps
            ];

            // Добавить торговое предложение
            $offerId = $obElement->Add($arOfferFields);

            if ($offerId) {

                // Добавить как товар
                try {
                    \Bitrix\Catalog\ProductTable::add([
                        'ID' => $offerId,
                        'VAT_INCLUDED' => "Y" // //НДС входит в стоимость
                    ]);
                } catch (Exception $e) {
                    $this->outError($e->getMessage());
                }

                try {
                    // Указывать цену
                    \CPrice::SetBasePrice($offerId, $offerPrice, "RUB");
                } catch (Exception $e) {
                    $this->outError($e->getMessage());
                }
            } else {
                $this->outError("Ошибка добавления торгового предложения: " . $obElement->LAST_ERROR);
                return;
            }
        } else {
            $this->outError("Ошибка добавления товара: " . $obElement->LAST_ERROR);
            return;
        }

    }

    function down()
    {
        // TODO: Implement down() method.
    }

}