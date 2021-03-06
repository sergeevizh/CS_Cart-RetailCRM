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

// rus_build_edost dbazhenov

$cities = array(
    'Абаза' => '842',
    'Абакан' => '839',
    'Абдулино' => '641',
    'Абинск' => '422',
    'Автуры' => '403',
    'Агидель' => '547',
    'Агрыз' => '592',
    'Азнакаево' => '579',
    'Азов' => '516',
    'Айхал' => '970',
    'Акбулак' => '652',
    'Аксай' => '532',
    'Алагир' => '390',
    'Алапаевск' => '726',
    'Алатырь' => '609',
    'Алдан' => '967',
    'Алейск' => '847',
    'Александров' => '27',
    'Александровск' => '663',
    'Александровское' => '471',
    'Алексеевка' => '2',
    'Алексин' => '232',
    'Алушта' => '2285',
    'Альметьевск' => '580',
    'Амурск' => '991',
    'Анадырь' => '2283',
    'Анапа' => '408',
    'Ангарск' => '885',
    'Анжеро-Судженск' => '907',
    'Анна' => '53',
    'Апатиты' => '326',
    'Апрелевка' => '154',
    'Апшеронск' => '425',
    'Арамиль' => '764',
    'Аргун' => '395',
    'Ардон' => '391',
    'Арзамас' => '622',
    'Арзгир' => '473',
    'Аркадак' => '707',
    'Армавир' => '409',
    'Армянск' => '2286',
    'Арсеньев' => '973',
    'Арск' => '593',
    'Артем' => '974',
    'Артемовский' => '727',
    'Архангельск' => '272',
    'Асбест' => '728',
    'Асино' => '952',
    'Астрахань' => '489',
    'Аткарск' => '696',
    'Афипский' => '447',
    'Ахтубинск' => '490',
    'Ахтырский' => '423',
    'Ачинск' => '861',
    'Ачхой-Мартан' => '399',
    'Аша' => '803',
    'Бавлы' => '581',
    'Багаевская' => '533',
    'Байкальск' => '902',
    'Байконур' => '2281',
    'Баймак' => '548',
    'Бакал' => '830',
    'Баксан' => '374',
    'Балабаново' => '75',
    'Балаково' => '697',
    'Балахна' => '623',
    'Балашиха' => '127',
    'Балашов' => '698',
    'Балезино' => '605',
    'Балей' => '957',
    'Балтийск' => '289',
    'Барабинск' => '932',
    'Барнаул' => '844',
    'Барыш' => '714',
    'Батайск' => '517',
    'Бахчисарай' => '2287',
    'Бачатский' => '909',
    'Бачи-Юрт' => '401',
    'Бежецк' => '217',
    'Безенчук' => '691',
    'Белая Глина' => '427',
    'Белая Калитва' => '518',
    'Белгород' => '1',
    'Белебей' => '549',
    'Белев' => '241',
    'Белово' => '908',
    'Белогорск' => '1001',
    'Белокуриха' => '848',
    'Белоозерский' => '131',
    'Белорецк' => '551',
    'Белореченск' => '410',
    'Белоярский' => '774',
    'Белый Яр' => '791',
    'Бердск' => '933',
    'Березники' => '664',
    'Березовка' => '877',
    'Березовский (Кемеровская область)' => '912',
    'Березовский (Свердловская область)' => '730',
    'Беслан' => '393',
    'Бийск' => '849',
    'Бикин' => '992',
    'Биробиджан' => '1018',
    'Бирск' => '552',
    'Благовещенск (Амурская область)' => '1000',
    'Благовещенск (Республика Башкортостан)' => '553',
    'Благодарный' => '474',
    'Бобров' => '54',
    'Богданович' => '731',
    'Богородицк' => '233',
    'Богородск' => '624',
    'Боготол' => '862',
    'Бодайбо' => '886',
    'Бокситогорск' => '295',
    'Бологое' => '218',
    'Болотное' => '938',
    'Большой Камень' => '976',
    'Бор' => '625',
    'Борзя' => '958',
    'Борисовка' => '8',
    'Борисоглебск' => '47',
    'Боровичи' => '339',
    'Боровский' => '772',
    'Бородино' => '863',
    'Братск' => '887',
    'Бронницы' => '101',
    'Брюховецкая' => '428',
    'Брянск' => '12',
    'Бугульма' => '582',
    'Бугуруслан' => '642',
    'Буденновск' => '459',
    'Бузулук' => '643',
    'Буинск' => '583',
    'Буй' => '82',
    'Буйнакск' => '355',
    'Бутурлиновка' => '55',
    'Валдай' => '341',
    'Валуйки' => '3',
    'Ванино' => '996',
    'Варениковская' => '437',
    'Васильево' => '594',
    'Великие Луки' => '347',
    'Великий Новгород' => '338',
    'Великий Устюг' => '283',
    'Вельск' => '279',
    'Венев' => '242',
    'Верещагино' => '674',
    'Верхнеднепровский' => '203',
    'Верхний Уфалей' => '804',
    'Верхняя Пышма' => '732',
    'Верхняя Салда' => '734',
    'Видное' => '144',
    'Вилючинск' => '1009',
    'Вихоревка' => '899',
    'Вичуга' => '62',
    'Владивосток' => '971',
    'Владикавказ' => '388',
    'Владимир' => '26',
    'Внуково' => '186',
    'Волгоград' => '494',
    'Волгодонск' => '519',
    'Волгореченск' => '83',
    'Волжск' => '571',
    'Волжский' => '496',
    'Вологда' => '282',
    'Волоколамск' => '129',
    'Волхов' => '296',
    'Вольск' => '699',
    'Воргашор' => '264',
    'Воркута' => '263',
    'Воронеж' => '45',
    'Воскресенск' => '130',
    'Воткинск' => '601',
    'Врангель' => '981',
    'Всеволожск' => '297',
    'Вуктыл' => '265',
    'Выборг' => '298',
    'Выкса' => '626',
    'Выселки' => '429',
    'Вышний Волочек' => '219',
    'Вяземский' => '998',
    'Вязники' => '28',
    'Вязьма' => '201',
    'Вятские Поляны' => '614',
    'Гаврилов-Ям' => '253',
    'Гагарин' => '202',
    'Гай' => '644',
    'Галич' => '84',
    'Гатчина' => '299',
    'Гвардейск' => '292',
    'Геленджик' => '411',
    'Георгиевск' => '460',
    'Гиагинская' => '351',
    'Глазов' => '602',
    'Голицыно' => '161',
    'Горно-Алтайск' => '831',
    'Горняк (Алтайский край)' => '857',
    'Горняк (Челябинская область)' => '812',
    'Городец' => '627',
    'Городище' => '501',
    'Гороховец' => '38',
    'Горьковский' => '495',
    'Горячеводский' => '469',
    'Горячий ключ' => '412',
    'Грамотеино' => '910',
    'Грибановский' => '56',
    'Грозный' => '394',
    'Грязи' => '97',
    'Грязовец' => '286',
    'Губаха' => '665',
    'Губкин' => '4',
    'Губкинский' => '794',
    'Гудермес' => '396',
    'Гуково' => '520',
    'Гулькевичи' => '430',
    'Гурьевск' => '913',
    'Гусев' => '293',
    'Гусиноозерск' => '837',
    'Гусь-Хрустальный' => '29',
    'Давлеканово' => '554',
    'Дагестанские Огни' => '356',
    'Далматово' => '720',
    'Дальнегорск' => '977',
    'Дальнереченск' => '978',
    'Данилов' => '254',
    'Данков' => '98',
    'Дегтярск' => '756',
    'Дедовск' => '139',
    'Дербент' => '357',
    'Десногорск' => '200',
    'Джалиль' => '599',
    'Джанкой' => '2288',
    'Дзержинск' => '628',
    'Дзержинский' => '102',
    'Дивногорск' => '864',
    'Дивное' => '472',
    'Димитровград' => '715',
    'Динская' => '431',
    'Дмитров' => '133',
    'Добрянка' => '666',
    'Долгопрудный' => '103',
    'Домодедово' => '134',
    'Донецк' => '521',
    'Донское' => '487',
    'Донской' => '234',
    'Дубна' => '104',
    'Дубовка' => '502',
    'Дугулубгей' => '375',
    'Дудинка' => '883',
    'Дюртюли' => '555',
    'Дятьково' => '13',
    'Евпатория' => '2289',
    'Егорлыкская' => '534',
    'Егорьевск' => '136',
    'Ейск' => '413',
    'Екатеринбург' => '724',
    'Елабуга' => '584',
    'Елань' => '503',
    'Елец' => '96',
    'Елизаветинская' => '407',
    'Елизово' => '1010',
    'Еманжелинск' => '805',
    'Емва' => '271',
    'Енисейск' => '865',
    'Ершов' => '708',
    'Ессентуки' => '461',
    'Ессентукская' => '484',
    'Ефремов' => '236',
    'Железноводск' => '462',
    'Железногорск (Красноярский край)' => '866',
    'Железногорск (Курская область)' => '89',
    'Железногорск-Илимский' => '900',
    'Железнодорожный' => '105',
    'Жердевка' => '214',
    'Жигулевск' => '682',
    'Жирновск' => '504',
    'Жуковка' => '18',
    'Жуковский' => '106',
    'Завитинск' => '1007',
    'Заводоуковск' => '768',
    'Заводской (Приморский край)' => '975',
    'Заводской (Республика Северная Осетия - Алания)' => '389',
    'Заволжье' => '633',
    'Заинск' => '585',
    'Заполярный' => '336',
    'Зарайск' => '137',
    'Заречный (Пензенская область)' => '656',
    'Заречный (Свердловская область)' => '735',
    'Заринск' => '850',
    'Зверево' => '522',
    'Зеленогорск' => '867',
    'Зеленоград' => '184',
    'Зеленодольск' => '586',
    'Зеленокумск' => '486',
    'Зеленчукская' => '385',
    'Зерноград' => '535',
    'Зея' => '1002',
    'Зима' => '888',
    'Зимовники' => '536',
    'Златоуст' => '806',
    'Знаменск' => '491',
    'Иваново' => '61',
    'Ивантеевка' => '107',
    'Ивдель' => '736',
    'Игра' => '606',
    'Ижевск' => '600',
    'Избербаш' => '358',
    'Излучинск' => '788',
    'Изобильный' => '476',
    'Иланский' => '878',
    'Ильский' => '448',
    'Инза' => '716',
    'Иноземцево' => '463',
    'Инта' => '266',
    'Ипатово' => '477',
    'Ирбит' => '737',
    'Иркутск' => '884',
    'Исилькуль' => '948',
    'Искитим' => '934',
    'Истра' => '138',
    'Ишим' => '769',
    'Ишимбай' => '556',
    'Йошкар-Ола' => '570',
    'Кавалерово' => '986',
    'Казань' => '578',
    'Кайеркан' => '873',
    'Калач' => '57',
    'Калач-на-Дону' => '505',
    'Калачинск' => '949',
    'Калининград' => '288',
    'Калининец' => '155',
    'Калинино' => '405',
    'Калининск' => '709',
    'Калтан' => '914',
    'Калуга' => '71',
    'Калязин' => '228',
    'Каменка' => '657',
    'Каменск-Уральский' => '738',
    'Каменск-Шахтинский' => '523',
    'Камень-на-Оби' => '851',
    'Камешково' => '39',
    'Камские Поляны' => '598',
    'Камызяк' => '492',
    'Камышин' => '497',
    'Камышлов' => '739',
    'Канаш' => '610',
    'Кандалакша' => '327',
    'Каневская' => '433',
    'Канск' => '868',
    'Кантышево' => '366',
    'Карабаново' => '36',
    'Карабаш' => '807',
    'Карабулак' => '365',
    'Карасук' => '940',
    'Карачаевск' => '384',
    'Карачев' => '19',
    'Карпинск' => '740',
    'Карталы' => '808',
    'Касимов' => '191',
    'Касли' => '809',
    'Каспийск' => '359',
    'Катав-Ивановск' => '810',
    'Катайск' => '721',
    'Качканар' => '741',
    'Кашин' => '220',
    'Кашира' => '140',
    'Кедровка' => '906',
    'Кемерово' => '905',
    'Кемь' => '256',
    'Керчь' => '2290',
    'Кизел' => '667',
    'Кизилюрт' => '360',
    'Кизляр' => '361',
    'Кимовск' => '237',
    'Кимры' => '221',
    'Кингисепп' => '300',
    'Кинель' => '683',
    'Кинель-Черкассы' => '692',
    'Кинешма' => '63',
    'Киреевск' => '243',
    'Киржач' => '40',
    'Кириши' => '301',
    'Киров (Калужская область)' => '72',
    'Киров (Кировская область)' => '613',
    'Кировград' => '742',
    'Кирово-Чепецк' => '615',
    'Кировск (Ленинградская область)' => '302',
    'Кировск (Мурманская область)' => '328',
    'Кирсанов' => '208',
    'Киселевск' => '915',
    'Кисловодск' => '464',
    'Климово' => '20',
    'Климовск' => '108',
    'Клин' => '141',
    'Клинцы' => '14',
    'Ковдор' => '334',
    'Ковров' => '30',
    'Ковылкино' => '575',
    'Когалым' => '775',
    'Кодинск' => '879',
    'Козельск' => '78',
    'Козьмодемьянск' => '572',
    'Коломна' => '109',
    'Колпашево' => '953',
    'Колпино' => '317',
    'Кольцово' => '725',
    'Кольчугино' => '31',
    'Коммунар' => '314',
    'Комсомольск-на-Амуре' => '993',
    'Комсомольский' => '577',
    'Конаково' => '222',
    'Кондопога' => '257',
    'Кондрово' => '76',
    'Константиновск' => '537',
    'Копейск' => '811',
    'Кораблино' => '194',
    'Кореновск' => '434',
    'Коркино' => '813',
    'Королев' => '110',
    'Корсаков' => '1013',
    'Коряжма' => '273',
    'Косая Гора' => '231',
    'Костомукша' => '258',
    'Кострома' => '81',
    'Котельники' => '148',
    'Котельниково' => '507',
    'Котельнич' => '616',
    'Котлас' => '274',
    'Котово' => '508',
    'Котовск' => '209',
    'Кохма' => '67',
    'Коченево' => '941',
    'Кочубеевское' => '479',
    'Красноармейск (Московская область)' => '111',
    'Красноармейск (Саратовская область)' => '700',
    'Красновишерск' => '675',
    'Красногвардейское' => '480',
    'Красногорск' => '142',
    'Краснодар' => '404',
    'Красное Село' => '319',
    'Краснознаменск' => '112',
    'Краснокаменск' => '959',
    'Краснокамск' => '668',
    'Краснообск' => '943',
    'Красноперекопск' => '2291',
    'Краснослободск' => '513',
    'Краснотурьинск' => '743',
    'Красноуральск' => '744',
    'Красноуфимск' => '745',
    'Красноярск' => '860',
    'Красный Кут' => '710',
    'Красный Сулин' => '524',
    'Кропоткин' => '414',
    'Крыловская' => '436',
    'Крымск' => '415',
    'Крюково' => '185',
    'Кстово' => '629',
    'Кубинка' => '162',
    'Кувандык' => '645',
    'Кудымкар' => '680',
    'Кузнецк' => '658',
    'Куйбышев' => '935',
    'Кукмор' => '595',
    'Кулебаки' => '630',
    'Кулешовка' => '531',
    'Кулунда' => '856',
    'Кумертау' => '557',
    'Кунгур' => '669',
    'Купино' => '942',
    'Курагино' => '880',
    'Курган' => '718',
    'Курганинск' => '438',
    'Куровское' => '164',
    'Курск' => '88',
    'Куртамыш' => '722',
    'Курчалой' => '400',
    'Курчатов' => '90',
    'Куса' => '829',
    'Кушва' => '746',
    'Кущевская' => '439',
    'Кызыл' => '838',
    'Кыштым' => '815',
    'Кяхта' => '836',
    'Лабинск' => '416',
    'Лабытнанги' => '795',
    'Лагань' => '382',
    'Ладожская' => '456',
    'Лакинск' => '34',
    'Лангепас' => '776',
    'Лебедянь' => '99',
    'Ленинградская' => '440',
    'Лениногорск' => '587',
    'Ленинск' => '509',
    'Ленинск-Кузнецкий' => '916',
    'Ленск' => '968',
    'Лермонтов' => '465',
    'Лесной' => '747',
    'Лесозаводск' => '979',
    'Лесосибирск' => '869',
    'Ливны' => '188',
    'Ликино-Дулево' => '165',
    'Линево' => '939',
    'Липецк' => '95',
    'Лиски' => '48',
    'Лобня' => '113',
    'Лодейное Поле' => '303',
    'Лосино-Петровский' => '182',
    'Луга' => '304',
    'Луховицы' => '146',
    'Лучегорск' => '987',
    'Лысково' => '634',
    'Лысьва' => '670',
    'Лыткарино' => '114',
    'Льгов' => '91',
    'Люберцы' => '147',
    'Людиново' => '73',
    'Лянтор' => '790',
    'Магадан' => '1011',
    'Магас' => '2284',
    'Магнитогорск' => '816',
    'Майкоп' => '350',
    'Майма' => '832',
    'Майский' => '376',
    'Малаховка' => '149',
    'Малая Вишера' => '342',
    'Малгобек' => '363',
    'Малоярославец' => '79',
    'Мантурово' => '85',
    'Мариинск' => '918',
    'Маркс' => '701',
    'Матвеев Курган' => '538',
    'Махачкала' => '354',
    'Мегион' => '777',
    'Медведево' => '573',
    'Медведовская' => '454',
    'Медвежьегорск' => '261',
    'Медногорск' => '646',
    'Межгорье' => '558',
    'Междуреченск' => '919',
    'Меленки' => '41',
    'Мелеуз' => '559',
    'Менделеевск' => '596',
    'Мензелинск' => '597',
    'Металлострой' => '318',
    'Миасс' => '817',
    'Миллерово' => '525',
    'Минеральные Воды' => '466',
    'Минусинск' => '870',
    'Мирный (Архангельская область)' => '275',
    'Мирный (Республика Саха (Якутия))' => '965',
    'Михайловка' => '498',
    'Михайловск' => '488',
    'Мичуринск' => '210',
    'Можайск' => '151',
    'Можга' => '603',
    'Моздок' => '392',
    'Монино' => '183',
    'Мончегорск' => '329',
    'Морозовск' => '539',
    'Моршанск' => '211',
    'Москва' => '1019',
    'Московский' => '145',
    'Мостовской' => '441',
    'Муравленко' => '796',
    'Мурманск' => '325',
    'Мурмаши' => '335',
    'Муром' => '32',
    'Мценск' => '189',
    'Мыски' => '920',
    'Мытищи' => '152',
    'Набережные Челны' => '588',
    'Навашино' => '635',
    'Навля' => '21',
    'Надым' => '797',
    'Назарово' => '871',
    'Назрань' => '364',
    'Нальчик' => '372',
    'Наро-Фоминск' => '153',
    'Нарткала' => '378',
    'Нарьян-Мар' => '281',
    'Нахабино' => '143',
    'Находка' => '980',
    'Невель' => '348',
    'Невельск' => '1014',
    'Невинномысск' => '467',
    'Невьянск' => '748',
    'Незлобная' => '475',
    'Нелидово' => '223',
    'Нерехта' => '86',
    'Нерчинск' => '962',
    'Нерюнгри' => '966',
    'Нестеровская' => '370',
    'Нефтегорск' => '693',
    'Нефтекамск' => '560',
    'Нефтекумск' => '481',
    'Нефтеюганск' => '778',
    'Нижневартовск' => '779',
    'Нижнекамск' => '589',
    'Нижнеудинск' => '889',
    'Нижний Ломов' => '660',
    'Нижний Новгород' => '621',
    'Нижний Тагил' => '749',
    'Нижняя Салда' => '750',
    'Нижняя Тура' => '751',
    'Никель' => '337',
    'Николаевск' => '510',
    'Николаевск-на-Амуре' => '994',
    'Никольск' => '661',
    'Никольско-Архангельский' => '128',
    'Никольское' => '316',
    'Новая Ляля' => '762',
    'Новая Усмань' => '58',
    'Новоалександровск' => '482',
    'Новоалтайск' => '852',
    'Новоаннинский' => '511',
    'Нововоронеж' => '49',
    'Новодвинск' => '276',
    'Новозыбков' => '15',
    'Новокубанск' => '442',
    'Новокузнецк' => '921',
    'Новокуйбышевск' => '684',
    'Новомичуринск' => '195',
    'Новомосковск' => '238',
    'Новопавловск' => '478',
    'Новопокровская' => '443',
    'Новороссийск' => '417',
    'Новосибирск' => '931',
    'Новосиликатный' => '845',
    'Новосинеглазовский' => '802',
    'Новотитаровская' => '432',
    'Новотроицк' => '647',
    'Новоузенск' => '711',
    'Новоульяновск' => '717',
    'Новоуральск' => '752',
    'Новочебоксарск' => '611',
    'Новочеркасск' => '526',
    'Новошахтинск' => '527',
    'Новый Городок' => '911',
    'Новый Оскол' => '9',
    'Новый Уренгой' => '798',
    'Ногинск' => '156',
    'Норильск' => '872',
    'Ноябрьск' => '799',
    'Нурлат' => '590',
    'Нытва' => '676',
    'Нягань' => '780',
    'Няндома' => '280',
    'Обнинск' => '74',
    'Обоянь' => '93',
    'Обь' => '936',
    'Одинцово' => '160',
    'Озерск' => '825',
    'Озеры' => '163',
    'Октябрьск' => '685',
    'Октябрьский' => '561',
    'Окуловка' => '343',
    'Оленегорск' => '330',
    'Омск' => '947',
    'Омутнинск' => '618',
    'Онега' => '277',
    'Орджоникидзевская' => '369',
    'Орел' => '187',
    'Оренбург' => '640',
    'Орехово-Зуево' => '115',
    'Орловский' => '542',
    'Орск' => '648',
    'Оса' => '677',
    'Осинники' => '922',
    'Осташков' => '224',
    'Остров' => '349',
    'Острогожск' => '50',
    'Отрадная' => '444',
    'Отрадное' => '315',
    'Отрадный' => '686',
    'Оха' => '1015',
    'Очер' => '678',
    'Павлово' => '631',
    'Павловск (Алтайский край)' => '858',
    'Павловск (Воронежская область)' => '59',
    'Павловск (Ленинградская область)' => '321',
    'Павловская' => '445',
    'Павловский Посад' => '166',
    'Палласовка' => '512',
    'Партизанск' => '982',
    'Пашковский' => '406',
    'Пенза' => '655',
    'Первомайск' => '636',
    'Первоуральск' => '753',
    'Пересвет' => '173',
    'Переславль-Залесский' => '248',
    'Пермь' => '662',
    'Персиановский' => '541',
    'Пестово' => '344',
    'Петергоф' => '322',
    'Петров Вал' => '506',
    'Петровск' => '702',
    'Петровск-Забайкальский' => '960',
    'Петрозаводск' => '255',
    'Петропавловск-Камчатский' => '1008',
    'Петушки' => '42',
    'Печора' => '267',
    'Пикалево' => '305',
    'Плавск' => '244',
    'Пласт' => '818',
    'Поворино' => '51',
    'Подольск' => '116',
    'Подпорожье' => '306',
    'Пойковский' => '787',
    'Покачи' => '781',
    'Покров' => '43',
    'Полевской' => '754',
    'Полтавская' => '435',
    'Полысаево' => '917',
    'Полярные Зори' => '331',
    'Полярный' => '332',
    'Поронайск' => '1016',
    'Похвистнево' => '687',
    'Почеп' => '22',
    'Приволжск' => '68',
    'Приволжский' => '706',
    'Придонской' => '46',
    'Приморско-Ахтарск' => '446',
    'Приозерск' => '307',
    'Приютово' => '550',
    'Прокопьевск' => '923',
    'Пролетарск' => '543',
    'Промышленная' => '928',
    'Протвино' => '117',
    'Прохладный' => '373',
    'Псков' => '346',
    'Пугачев' => '703',
    'Пушкин' => '323',
    'Пушкино' => '168',
    'Пущино' => '118',
    'Пыть-Ях' => '782',
    'Пятигорск' => '468',
    'Радужный (Владимирская область)' => '35',
    'Радужный (Ханты-Мансийский автономный округ)' => '783',
    'Раевский' => '568',
    'Разумное' => '7',
    'Райчихинск' => '1003',
    'Раменское' => '170',
    'Рассказово' => '212',
    'Ревда' => '755',
    'Реж' => '757',
    'Реутов' => '119',
    'Рефтинский' => '729',
    'Ржев' => '225',
    'Родники' => '69',
    'Роза' => '814',
    'Рославль' => '204',
    'Россошь' => '52',
    'Ростов' => '249',
    'Ростов-на-Дону' => '515',
    'Рошаль' => '120',
    'Ртищево' => '704',
    'Рубцовск' => '853',
    'Рузаевка' => '576',
    'Рыбинск' => '250',
    'Рыбное' => '196',
    'Рыльск' => '94',
    'Ряжск' => '197',
    'Рязань' => '190',
    'Саки' => '2292',
    'Салават' => '562',
    'Салехард' => '793',
    'Сальск' => '528',
    'Самара' => '681',
    'Санкт-Петербург' => '1020',
    'Саракташ' => '653',
    'Саранск' => '574',
    'Сарапул' => '604',
    'Саратов' => '695',
    'Саров' => '632',
    'Сасово' => '192',
    'Сатка' => '819',
    'Сафоново' => '205',
    'Саяногорск' => '840',
    'Саянск' => '890',
    'Светлоград' => '483',
    'Светлый' => '290',
    'Светогорск' => '313',
    'Свирск' => '897',
    'Свободный' => '1004',
    'Свободы' => '470',
    'Севастополь' => '2293',
    'Северо-Задонск' => '235',
    'Северобайкальск' => '834',
    'Северодвинск' => '278',
    'Североморск' => '333',
    'Североуральск' => '758',
    'Северск' => '954',
    'Северская' => '449',
    'Сегежа' => '259',
    'Селенгинск' => '835',
    'Сельцо' => '16',
    'Семенов' => '637',
    'Семикаракорск' => '544',
    'Семилуки' => '60',
    'Сергач' => '638',
    'Сергиев Посад' => '172',
    'Сердобск' => '659',
    'Серов' => '759',
    'Серпухов' => '121',
    'Сертолово' => '308',
    'Сестрорецк' => '320',
    'Сибай' => '563',
    'Сим' => '827',
    'Симферополь' => '2294',
    'Скопин' => '193',
    'Славгород' => '854',
    'Славянка' => '988',
    'Славянск-на-Кубани' => '418',
    'Сланцы' => '309',
    'Слободской' => '617',
    'Слюдянка' => '901',
    'Смоленск' => '199',
    'Снежинск' => '826',
    'Собинка' => '33',
    'Советск (Калининградская область)' => '291',
    'Советск (Кировская область)' => '619',
    'Советская Гавань' => '995',
    'Советский' => '789',
    'Сокол' => '284',
    'Соликамск' => '671',
    'Солнечногорск' => '175',
    'Солнечный' => '999',
    'Соль-Илецк' => '649',
    'Сорочинск' => '650',
    'Сортавала' => '260',
    'Сосновоборск' => '875',
    'Сосновый Бор' => '310',
    'Сосногорск' => '268',
    'Софрино' => '169',
    'Сочи' => '419',
    'Спасск-Дальний' => '983',
    'Среднеуральск' => '733',
    'Ставрополь' => '458',
    'Старая Купавна' => '159',
    'Старая Русса' => '340',
    'Стародуб' => '23',
    'Староминская' => '450',
    'Старощербиновская' => '457',
    'Старый Оскол' => '5',
    'Степное' => '712',
    'Стерлитамак' => '564',
    'Стрежевой' => '955',
    'Строитель (Белгородская область)' => '11',
    'Строитель (Тамбовская область)' => '215',
    'Струнино' => '37',
    'Ступино' => '176',
    'Суворов' => '245',
    'Суворовская' => '485',
    'Судак' => '2295',
    'Сузун' => '944',
    'Сургут' => '784',
    'Суровикино' => '514',
    'Сурхахи' => '367',
    'Сухиничи' => '80',
    'Суходол' => '694',
    'Сухой Лог' => '760',
    'Сходня' => '178',
    'Сызрань' => '688',
    'Сыктывкар' => '262',
    'Сысерть' => '763',
    'Тавда' => '761',
    'Таганрог' => '529',
    'Тайга' => '924',
    'Тайшет' => '891',
    'Талица' => '765',
    'Талнах' => '874',
    'Тальменка' => '859',
    'Тамбов' => '207',
    'Тара' => '950',
    'Тарко-Сале' => '800',
    'Татарск' => '937',
    'Таштагол' => '925',
    'Тбилисская' => '451',
    'Тверь' => '216',
    'Тейково' => '64',
    'Темрюк' => '452',
    'Терек' => '377',
    'Тимашевск' => '453',
    'Тихвин' => '311',
    'Тихорецк' => '420',
    'Тобольск' => '770',
    'Товарково' => '77',
    'Тогучин' => '945',
    'Тольятти' => '689',
    'Томилино' => '150',
    'Томск' => '951',
    'Топки' => '926',
    'Торжок' => '226',
    'Торопец' => '229',
    'Тосно' => '312',
    'Тоцкое Второе' => '654',
    'Трехгорный' => '824',
    'Троицк (Московская область)' => '122',
    'Троицк (Челябинская область)' => '820',
    'Троицкая' => '371',
    'Трубчевск' => '24',
    'Трудовое' => '972',
    'Туапсе' => '421',
    'Туймазы' => '565',
    'Тула' => '230',
    'Тулун' => '892',
    'Туринск' => '766',
    'Тутаев' => '251',
    'Тучково' => '171',
    'Тында' => '1005',
    'Тырныауз' => '380',
    'Тюмень' => '767',
    'Тяжинский' => '929',
    'Ува' => '607',
    'Уварово' => '213',
    'Углич' => '252',
    'Удачный' => '969',
    'Удомля' => '227',
    'Ужур' => '881',
    'Узловая' => '239',
    'Улан-Удэ' => '833',
    'Ульяновск' => '713',
    'Унеча' => '25',
    'Урай' => '785',
    'Урус-Мартан' => '397',
    'Урюпинск' => '499',
    'Усинск' => '269',
    'Усмань' => '100',
    'Усолье-Сибирское' => '893',
    'Уссурийск' => '984',
    'Усть-Абакан' => '843',
    'Усть-Джегута' => '387',
    'Усть-Илимск' => '894',
    'Усть-Катав' => '821',
    'Усть-Кут' => '895',
    'Усть-Лабинск' => '455',
    'Усть-Ордынский' => '904',
    'Уфа' => '546',
    'Ухта' => '270',
    'Учалы' => '566',
    'Учкекен' => '386',
    'Федоровский' => '792',
    'Феодосия' => '2296',
    'Фокино (Брянская область)' => '17',
    'Фокино (Приморский край)' => '985',
    'Фролово' => '500',
    'Фрязино' => '123',
    'Фурманов' => '65',
    'Хабаровск' => '990',
    'Хадыженск' => '426',
    'Ханты-Мансийск' => '773',
    'Харабали' => '493',
    'Хасавюрт' => '362',
    'Химки' => '177',
    'Холмск' => '1017',
    'Холмская' => '424',
    'Хотьково' => '174',
    'Цимлянск' => '545',
    'Цоцин-Юрт' => '402',
    'Чайковский' => '672',
    'Чалтырь' => '540',
    'Чапаевск' => '690',
    'Чебаркуль' => '822',
    'Чебоксары' => '608',
    'Чегдомын' => '997',
    'Чегем' => '379',
    'Челябинск' => '801',
    'Черемхово' => '896',
    'Черепаново' => '946',
    'Череповец' => '285',
    'Черкесск' => '383',
    'Черниговка' => '989',
    'Черноголовка' => '157',
    'Черногорск' => '841',
    'Чернушка' => '679',
    'Чернянка' => '10',
    'Черняховск' => '294',
    'Чехов' => '179',
    'Чистополь' => '591',
    'Чита' => '956',
    'Чишмы' => '569',
    'Чудово' => '345',
    'Чунский' => '903',
    'Чусовой' => '673',
    'Шадринск' => '719',
    'Шали' => '398',
    'Шарыпово' => '876',
    'Шарья' => '87',
    'Шатура' => '180',
    'Шахты' => '530',
    'Шахунья' => '639',
    'Шебекино' => '6',
    'Шексна' => '287',
    'Шелехов' => '898',
    'Шерловая Гора' => '961',
    'Шилка' => '963',
    'Шилово' => '198',
    'Шимановск' => '1006',
    'Шумерля' => '612',
    'Шумиха' => '723',
    'Шушары' => '324',
    'Шушенское' => '882',
    'Шуя' => '66',
    'Щекино' => '240',
    'Щелково' => '181',
    'Щербинка' => '124',
    'Щигры' => '92',
    'Экажево' => '368',
    'Электрогорск' => '167',
    'Электросталь' => '125',
    'Электроугли' => '158',
    'Элиста' => '381',
    'Энгельс' => '705',
    'Энем' => '352',
    'Юбилейный' => '126',
    'Югорск' => '786',
    'Южа' => '70',
    'Южно-Сахалинск' => '1012',
    'Южноуральск' => '823',
    'Южный' => '846',
    'Юрга' => '927',
    'Юрьев-Польский' => '44',
    'Юрюзань' => '828',
    'Яблоновский' => '353',
    'Якутск' => '964',
    'Ялта' => '2297',
    'Ялуторовск' => '771',
    'Янаул' => '567',
    'Яранск' => '620',
    'Яровое' => '855',
    'Ярославль' => '247',
    'Ярцево' => '206',
    'Ясногорск' => '246',
    'Ясный' => '651',
    'Яшкино' => '930'
);

return $cities;
