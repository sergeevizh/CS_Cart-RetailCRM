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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Hook handler for check detail image sizes ration
 *
 * @param array $image_data Image data
 * @param array $images Array with initial images
 * @param int $image_width Result image width
 * @param int $image_height Result image height
 * @return boolean Always true
 */
function fn_image_zoom_image_to_display_post(&$image_data, &$images, &$image_width, &$image_height)
{
    if (!empty($images['detailed']) && !empty($image_data['detailed_image_path'])) {
        $object_type = isset($images['detailed']['object_type']) ? $images['detailed']['object_type'] : '';

        // Regenerate detailed images only if we generate product thumbnails (compare by size - it's dirty, yes)
        if ($object_type != 'product' || !in_array($image_width, array(
            Registry::get('settings.Thumbnails.product_details_thumbnail_width'),
            Registry::get('settings.Thumbnails.product_quick_view_thumbnail_width'))
        )) {
            return true;
        }

        fn_image_zoom_check_image($image_data, $images);
    }

    return true;
}

/**
 * Check detail image sizes ration
 *
 * @param array $image_data Image data
 * @param array $images Array with initial images
 */
function fn_image_zoom_check_image(&$image_data, &$images)
{
    $precision = 80;

    $ratio_detailed = round(round($images['detailed']['image_x'] / $images['detailed']['image_y'] * $precision) / $precision, 2);
    $ratio_original = round(round($image_data['width'] / $image_data['height'] * $precision) / $precision, 2);

    if ($ratio_detailed != $ratio_original) {
        if ($ratio_detailed < $ratio_original) {
            $new_x = ceil($images['detailed']['image_y'] / $image_data['height'] * $image_data['width']);
            $new_y = $images['detailed']['image_y'];
        } else {
            $new_y = ceil($images['detailed']['image_x'] / $image_data['width'] * $image_data['height']);
            $new_x = $images['detailed']['image_x'];
        }

        $file_path = fn_generate_thumbnail($images['detailed']['relative_path'], $new_x, $new_y, false, true);

        /**
         * Post hook for check detail image sizes ration
         * @param string $file_path File path
         * @param array $image_data Image data
         * @param array $images Array with initial images
         */
        fn_set_hook('image_zoom_check_image_post', $file_path, $image_data, $images);

        if ($file_path) {
            $image_data['detailed_image_path'] = \Tygh\Storage::instance('images')->getUrl($file_path);
        }
    }
}
