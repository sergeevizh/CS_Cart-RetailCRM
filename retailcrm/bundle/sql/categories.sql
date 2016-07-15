SELECT
  `categories`.`category_id` AS `id`,
  `seo_names`.`name` AS `url`,
  `parent_id` AS `parentId`,
  `category` AS `name`
FROM `categories`
  INNER JOIN `category_descriptions` ON `category_descriptions`.`category_id` = `categories`.`category_id`
  INNER JOIN `seo_names` ON `seo_names`.`object_id` = `categories`.`category_id`
WHERE `type` = 'c';