<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

CModule::IncludeModule('iblock');
CModule::IncludeModule('highloadblock');

$hlblock = HL\HighloadBlockTable::getById(2)->fetch(); // id highload блока
$entity = HL\HighloadBlockTable::compileEntity($hlblock);
$entityClass = $entity->getDataClass();
$res = $entityClass::getList(array(
    'select' => array('*'),
    'order' => array('ID' => 'ASC'),
    'filter' => array()));
$periodHL = [];

if ($res) {
    while ($hlRes = $res->fetch()) {
        $periodHL[] = $hlRes;
    }
}


/*------------------------------------------*/
$iBlockId = '15'; // id инфоблока Путёвки

$arFilter = array('ACTIVE' => 'Y', 'IBLOCK_ID' => $iBlockId,
    array(
        'LOGIC' => 'AND',
        array('PROPERTY_58_VALUE' => 'Y'),
    ),
);
$arOrder = ['SORT' => 'ASC'];
$arSelect = array(
    'ID', 'IBLOCK_ID', 'NAME', 'CODE'
);

$res = CIBlockElement::GetList($arOrder, $arFilter, $arSelect);
$voucherList = [];
$voucherID = [];  // список всех путёвок, которые можно отображать в номерах
$i = 0;
while ($arRes = $res->GetNextElement()) {
    $voucherList[$i] = $arRes->GetFields();
    $voucherID[] = $voucherList[$i]['ID'];
    $i++;
}
unset($res);
unset($arRes);
/*------------------------------*/


if (!empty($arResult)) {
    $iBlockId = '16'; // id инфоблока цена

    $arFilter = array('ACTIVE' => 'Y', 'IBLOCK_ID' => $iBlockId,
        array(
            'LOGIC' => 'AND',
            array('PROPERTY_ROOMS' => $arResult['ID']),
        ),
        array(
            'LOGIC' => 'AND',
            array('PROPERTY_VOUCHER' => $voucherID),
        ),
    );
    $arOrder = ['SORT' => 'ASC'];
    $arSelect = array(
        'ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_*'
    );
    $GroupBy = array('PROPERTY_VOUCHER');

    $res = CIBlockElement::GetList($arOrder, $arFilter, false, Array("nPageSize" => 50), $arSelect);
    $allPrice = [];
    $minPrice = [];
    $categoryMinPrice = [];
    $periods = [];
    $i = 0;
    while ($arRes = $res->GetNextElement()) {
        if ($arRes) {
            $arFields = $arRes->GetFields();
            $arProps = $arRes->GetProperties();

            foreach ($voucherList as $voucher) {
                if (in_array($voucher['ID'], $arProps['VOUCHER']['VALUE']) && !empty($periodHL)) {
                    $periodName = '';
                    $periodPrice = '';
                    foreach ($arProps['PERIOD']['VALUE'] as $key => $voucherPeriod) {
                        foreach ($periodHL as $period) {

                            if ($voucherPeriod === $period['UF_XML_ID']) {
                                if ($key == 0) {
                                    $dateFrom = ConvertDateTime($period['UF_DATE_FROM'], "DD.MM");
                                    $dateTo = ConvertDateTime($period['UF_DATE_TO'], "DD.MM");
                                    $data = $dateFrom . '—' . $dateTo;
                                    $periodName = $data;
                                    $minPrice[] = $arProps['PRICE']['VALUE'];
                                } else {
                                    $dateFrom = ConvertDateTime($period['UF_DATE_FROM'], "DD.MM");
                                    $dateTo = ConvertDateTime($period['UF_DATE_TO'], "DD.MM");
                                    $data = $dateFrom . '—' . $dateTo;
                                    $periodName .= '<br>' . $data;
                                    $minPrice[] = $arProps['PRICE']['VALUE'];
                                }
                            }
                        }
                    }
                    $allPrice[$voucher['NAME']][$periodName] = $arProps['PRICE']['VALUE'];

                    $minPrice[] = $arProps['PRICE']['VALUE'];
                    $periods[] = $periodName;
                    $categoryMinPrice[$voucher['CODE']][] = min($minPrice); // Минимальная цена для конкретной категории
                    $minPrice = [];
                }
            }
        }
        $i++;
    }
    $periods = array_unique($periods); // Массив периодов для шапки таблицы
    $arResult['PRICES'] = $allPrice;
    $arResult['PERIODS'] = $periods;

    foreach ($categoryMinPrice as $key => $minPrice) {
        $arResult['MIN_PRICES'][$key] = min($minPrice);
    }

    unset($voucherList);
    unset($voucherID);
    unset($categoryMinPrice);
    unset($allPrice);
    unset($minPrice);
    unset($periodHL);
}