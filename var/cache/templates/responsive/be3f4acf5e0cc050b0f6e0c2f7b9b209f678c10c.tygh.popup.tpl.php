<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:26:41
         compiled from "/var/www/local.s3.prefest.ru/design/themes/responsive/templates/addons/call_requests/views/call_requests/components/popup.tpl" */ ?>
<?php /*%%SmartyHeaderCode:431816755786176172b8f0-18127867%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'be3f4acf5e0cc050b0f6e0c2f7b9b209f678c10c' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/themes/responsive/templates/addons/call_requests/views/call_requests/components/popup.tpl',
      1 => 1468405247,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '431816755786176172b8f0-18127867',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'product' => 0,
    'block' => 0,
    'id' => 0,
    'obj_prefix' => 0,
    'suffix' => 0,
    'link_text' => 0,
    'text' => 0,
    'link_meta' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_57861761786209_48976702',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57861761786209_48976702')) {function content_57861761786209_48976702($_smarty_tpl) {?><?php if (!is_callable('smarty_function_set_id')) include '/var/www/local.s3.prefest.ru/app/functions/smarty_plugins/function.set_id.php';
?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {
$_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start();
$_smarty_tpl->tpl_vars['suffix'] = new Smarty_variable('', null, 0);?>
<?php if ($_smarty_tpl->tpl_vars['product']->value) {?>
    <?php $_smarty_tpl->tpl_vars['suffix'] = new Smarty_variable($_smarty_tpl->tpl_vars['product']->value['product_id'], null, 0);?>
<?php } elseif ($_smarty_tpl->tpl_vars['block']->value) {?>
    <?php $_smarty_tpl->tpl_vars['suffix'] = new Smarty_variable($_smarty_tpl->tpl_vars['block']->value['snapping_id'], null, 0);?>
<?php }?>

<?php $_smarty_tpl->tpl_vars['id'] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['id']->value)===null||$tmp==='' ? "call_request_".((string)$_smarty_tpl->tpl_vars['obj_prefix']->value).((string)$_smarty_tpl->tpl_vars['suffix']->value) : $tmp), null, 0);?>

<?php $_smarty_tpl->_capture_stack[0][] = array("call_request_popup", null, null); ob_start(); ?>
    <?php echo $_smarty_tpl->getSubTemplate ("addons/call_requests/views/call_requests/components/call_requests_content.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('product'=>$_smarty_tpl->tpl_vars['product']->value), 0);?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php echo $_smarty_tpl->getSubTemplate ("common/popupbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('content'=>Smarty::$_smarty_vars['capture']['call_request_popup'],'link_text'=>$_smarty_tpl->tpl_vars['link_text']->value,'text'=>(($tmp = @$_smarty_tpl->tpl_vars['text']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['link_text']->value : $tmp),'id'=>$_smarty_tpl->tpl_vars['id']->value,'link_meta'=>$_smarty_tpl->tpl_vars['link_meta']->value), 0);
list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();
if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {
if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="addons/call_requests/views/call_requests/components/popup.tpl" id="<?php echo smarty_function_set_id(array('name'=>"addons/call_requests/views/call_requests/components/popup.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else {
echo Smarty::$_smarty_vars['capture']['template_content'];
}
}
} else {
$_smarty_tpl->tpl_vars['suffix'] = new Smarty_variable('', null, 0);?>
<?php if ($_smarty_tpl->tpl_vars['product']->value) {?>
    <?php $_smarty_tpl->tpl_vars['suffix'] = new Smarty_variable($_smarty_tpl->tpl_vars['product']->value['product_id'], null, 0);?>
<?php } elseif ($_smarty_tpl->tpl_vars['block']->value) {?>
    <?php $_smarty_tpl->tpl_vars['suffix'] = new Smarty_variable($_smarty_tpl->tpl_vars['block']->value['snapping_id'], null, 0);?>
<?php }?>

<?php $_smarty_tpl->tpl_vars['id'] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['id']->value)===null||$tmp==='' ? "call_request_".((string)$_smarty_tpl->tpl_vars['obj_prefix']->value).((string)$_smarty_tpl->tpl_vars['suffix']->value) : $tmp), null, 0);?>

<?php $_smarty_tpl->_capture_stack[0][] = array("call_request_popup", null, null); ob_start(); ?>
    <?php echo $_smarty_tpl->getSubTemplate ("addons/call_requests/views/call_requests/components/call_requests_content.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('product'=>$_smarty_tpl->tpl_vars['product']->value), 0);?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php echo $_smarty_tpl->getSubTemplate ("common/popupbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('content'=>Smarty::$_smarty_vars['capture']['call_request_popup'],'link_text'=>$_smarty_tpl->tpl_vars['link_text']->value,'text'=>(($tmp = @$_smarty_tpl->tpl_vars['text']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['link_text']->value : $tmp),'id'=>$_smarty_tpl->tpl_vars['id']->value,'link_meta'=>$_smarty_tpl->tpl_vars['link_meta']->value), 0);
}?><?php }} ?>
