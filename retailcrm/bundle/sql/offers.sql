SELECT
    `pd`.`product_id` AS `id`,
    `pd`.`product_id` AS `productId`,
    `amount` AS `quantity`,
    `name` AS `url`,
    `price` AS `price`,
    `category_id` AS `categoryId`,
    `image_path` AS `picture`,
    `product` AS `name`,
    `product` AS `productName`,
    `product_code` AS `article`

FROM `products` AS `p`
    INNER JOIN `product_descriptions` AS `pd` ON `pd`.`product_id` = `p`.`product_id`
    INNER JOIN `product_prices` AS `pp` ON `pp`.`product_id` = `p`.`product_id`
    INNER JOIN `products_categories` AS `pc` ON  `pc`.`product_id` = `p`.`product_id`

    INNER JOIN `images_links` AS `il` ON `il`.`object_id` = `p`.`product_id`
    INNER JOIN `images` AS `i` ON `i`.`image_id` = `il`.`detailed_id`
    LEFT JOIN `seo_names` AS `sn` ON `sn`.`object_id` = `p`.`product_id`
WHERE `sn`.`type` = 'p'
GROUP BY `id`
ORDER BY `id`