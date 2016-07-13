<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

$sdek_delivery = array(
    '136' => array(
        'code' => '136',
        'tariff' => 'Посылка склад-склад',
        'terminals' => 'Y'
    ),
    '137' => array(
        'code' => '137',
        'tariff' => 'Посылка склад-дверь',
        'terminals' => 'N'
    ),
    '138' => array(
        'code' => '138',
        'tariff' => 'Посылка дверь-склад',
        'terminals' => 'Y'
    ),
    '139' => array(
        'code' => '139',
        'tariff' => 'Посылка дверь-дверь',
        'terminals' => 'N'
    ),
    '233' => array(
        'code' => '233',
        'tariff' => 'Экономичная посылка склад-дверь',
        'terminals' => 'N'
    ),
    '234' => array(
        'code' => '234',
        'tariff' => 'Экономичная посылка склад-склад',
        'terminals' => 'Y'
    ),
    '301' => array(
        'code' => '301',
        'tariff' => 'Постомат InPost дверь-склад',
        'terminals' => 'Y',
        'postomat' => 'Y'
    ),
    '302' => array(
        'code' => '302',
        'tariff' => 'Постомат InPost склад-склад',
        'terminals' => 'Y',
        'postomat' => 'Y'
    )
);

return $sdek_delivery;
