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

use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_attachments($object_type, $object_id, $type = 'M', $lang_code = CART_LANGUAGE)
{
    $condition = '';
    if (AREA != 'A') {
        $auth = Tygh::$app['session']['auth'];
        $condition = ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'usergroup_ids', true) . ") AND status = 'A'";
    }

    return db_get_array("SELECT ?:attachments.*, ?:attachment_descriptions.description FROM ?:attachments LEFT JOIN ?:attachment_descriptions ON ?:attachments.attachment_id = ?:attachment_descriptions.attachment_id AND lang_code = ?s WHERE object_type = ?s AND object_id = ?i AND type = ?s ?p ORDER BY position", $lang_code, $object_type, $object_id, $type, $condition);
}

function fn_update_attachments($attachment_data, $attachment_id, $object_type, $object_id, $type = 'M', $files = null, $lang_code = DESCR_SL)
{
    $object_id = intval($object_id);
    $directory = $object_type . '/' . $object_id;

    if ($files != null) {
        $uploaded_data = $files;
    } else {
        $uploaded_data = fn_filter_uploaded_data('attachment_files');
    }

    if (!empty($attachment_id)) {

        $rec = array (
            'usergroup_ids' => empty($attachment_data['usergroup_ids']) ? '0' : implode(',', $attachment_data['usergroup_ids']),
            'position' => $attachment_data['position']
        );

        db_query("UPDATE ?:attachment_descriptions SET description = ?s WHERE attachment_id = ?i AND lang_code = ?s", $attachment_data['description'], $attachment_id, $lang_code);
        db_query("UPDATE ?:attachments SET ?u WHERE attachment_id = ?i AND object_type = ?s AND object_id = ?i AND type = ?s", $rec, $attachment_id, $object_type, $object_id, $type);

        fn_set_hook('attachment_update_file', $attachment_data, $attachment_id, $object_type, $object_id, $type, $files, $lang_code, $uploaded_data);
    } elseif (!empty($uploaded_data)) {
        $rec = array (
            'object_type' => $object_type,
            'object_id' => $object_id,
            'usergroup_ids' => empty($attachment_data['usergroup_ids']) ? '0' : implode(',', $attachment_data['usergroup_ids']),
            'position' => $attachment_data['position']
        );

        if ($type !== null) {
            $rec['type'] = $type;
        } elseif (!empty($attachment_data['type'])) {
            $rec['type'] = $attachment_data['type'];
        }

        $attachment_id = db_query("INSERT INTO ?:attachments ?e", $rec);

        if ($attachment_id) {
            // Add file description
            foreach (fn_get_translation_languages() as $lang_code => $v) {
                $rec = array (
                    'attachment_id' => $attachment_id,
                    'lang_code' => $lang_code,
                    'description' => is_array($attachment_data['description']) ? $attachment_data['description'][$lang_code] : $attachment_data['description']
                );

                db_query("INSERT INTO ?:attachment_descriptions ?e", $rec);
            }

            $uploaded_data[$attachment_id] = $uploaded_data[0];
            unset($uploaded_data[0]);
        }

        fn_set_hook('attachment_add_file', $attachment_data, $object_type, $object_id, $type, $files, $attachment_id, $uploaded_data);
    }

    if ($attachment_id && !empty($uploaded_data[$attachment_id]) && $uploaded_data[$attachment_id]['size']) {
        $filename = $uploaded_data[$attachment_id]['name'];
        $old_filename = db_get_field("SELECT filename FROM ?:attachments WHERE attachment_id = ?i", $attachment_id);

        if ($old_filename) {
            Storage::instance('attachments')->delete($directory . '/' . $old_filename);
        }

        list($filesize, $filename) = Storage::instance('attachments')->put($directory . '/' . $filename, array(
            'file' => $uploaded_data[$attachment_id]['path']
        ));

        if ($filesize) {
            $filename = fn_basename($filename);
            db_query("UPDATE ?:attachments SET filename = ?s, filesize = ?i WHERE attachment_id = ?i", $filename, $filesize, $attachment_id);
        }
    }

    return $attachment_id;
}

function fn_delete_attachments($attachment_ids, $object_type, $object_id)
{
    fn_set_hook('attachment_delete_file', $attachment_ids, $object_type, $object_id);

    $data = db_get_array("SELECT * FROM ?:attachments WHERE attachment_id IN (?n) AND object_type = ?s AND object_id = ?i", $attachment_ids, $object_type, $object_id);

    foreach ($data as $entry) {
        Storage::instance('attachments')->delete($entry['object_type'] . '/' . $object_id . '/' . $entry['filename']);
    }

    db_query("DELETE FROM ?:attachments WHERE attachment_id IN (?n) AND object_type = ?s AND object_id = ?i", $attachment_ids, $object_type, $object_id);
    db_query("DELETE FROM ?:attachment_descriptions WHERE attachment_id IN (?n)", $attachment_ids);

    return true;
}

function fn_get_attachment($attachment_id)
{
    $auth = Tygh::$app['session']['auth'];

    $condition = '';
    if (AREA != 'A') {
        $condition = ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'usergroup_ids', true) . ") AND status = 'A'";
    }

    $data = db_get_row("SELECT * FROM ?:attachments WHERE attachment_id = ?i ?p", $attachment_id, $condition);

    fn_set_hook('attachments_get_attachment', $data, $attachment_id);

    if (!empty($data)) {
        Storage::instance('attachments')->get($data['object_type'] . '/' . $data['object_id'] . '/' . $data['filename']);
    }
    exit;
}

/**
 * Function clone product's attachments
 *
 * @param int $product_id old product id
 * @param int $pid new product id
 */
function fn_attachments_clone_product(&$product_id, &$pid)
{
    $add_data = array();
    $attachments = db_get_array("SELECT * FROM ?:attachments WHERE object_type = 'product' AND object_id = ?i", $product_id);

    foreach ($attachments as &$attachment) {
        $attachment_descriptions = db_get_array("SELECT * FROM ?:attachment_descriptions WHERE attachment_id = ?i", $attachment['attachment_id']);

        $attachment['attachment_id'] = 0;
        $attachment['object_id'] = $pid;

        $attachment_id = db_query("INSERT INTO ?:attachments ?e", $attachment);

        Storage::instance('attachments')->copy('product/' . $product_id, 'product/' . $pid);

        foreach ($attachment_descriptions as $descr) {
            $descr['attachment_id'] = $attachment_id;
            db_query("INSERT INTO ?:attachment_descriptions ?e", $descr);
        }
    }
}

/**
 * Function delete product's attachments
 *
 * @param int $product_id product id
 */
function fn_attachments_delete_product_post(&$product_id)
{
    $attachments = db_get_fields("SELECT attachment_id FROM ?:attachments WHERE object_type = 'product' AND object_id = ?i", $product_id);

    Storage::instance('attachments')->deleteDir('product/' . $product_id);

    foreach ($attachments as $attachment_id) {
        db_query("DELETE FROM ?:attachments WHERE attachment_id = ?i", $attachment_id);
        db_query("DELETE FROM ?:attachment_descriptions WHERE attachment_id = ?i", $attachment_id);
    }
}

/**
 * Checks permission to work with the attachment
 *
 * @param array $request Array of query parameters
 * @return bool Permission to work with attachment
 */
function fn_attachments_check_permission($request)
{
    /**
     * Changes input parameters for attachment permission check
     *
     * @param array $request Array of query parameters
     */
    fn_set_hook('attachments_check_permission_pre', $request);

    $permission = false;

    if (!empty($request['object_type']) && !empty($request['object_id'])) {
        $table = "products";
        $field = "product_id";
        $condition = "AND {$field} = {$request['object_id']} " . fn_get_company_condition("?:{$table}.company_id");

        /**
         * Checks permission to work with the attachment
         *
         * @param array $request Array of query parameters
         * @param string $table Table to perform check
         * @param string $field SQL field to be selected in an SQL-query
         * @param string $condition String containing SQL-query condition prepended with a logical operator (AND or OR)
         */
        fn_set_hook('attachments_check_permission', $request, $table, $field, $condition);
        $object_id = db_get_field(
            "SELECT ?f FROM ?:?f WHERE 1 ?p",
            $field,
            $table,
            $condition
        );
        if (!empty($object_id)) {
            $permission = true;
        }
    }

    /**
     * Changes result of attachment permission check
     *
     * @param array $request Array of query parameters
     */
    fn_set_hook('attachments_check_permission_post', $request, $permission);
    return $permission;
}