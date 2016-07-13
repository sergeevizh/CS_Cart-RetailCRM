<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:26:38
         compiled from "/var/www/local.s3.prefest.ru/design/themes/responsive/templates/meta.tpl" */ ?>
<?php /*%%SmartyHeaderCode:16581678325786175edc5953-59756875%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0fcbddc0b0602b815bc985c7c45589787824c21c' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/themes/responsive/templates/meta.tpl',
      1 => 1468405241,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '16581678325786175edc5953-59756875',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'display_base_href' => 0,
    'config' => 0,
    'store_trigger' => 0,
    'meta_description' => 0,
    'location_data' => 0,
    'meta_keywords' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_5786175ee12635_29741235',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5786175ee12635_29741235')) {function content_5786175ee12635_29741235($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/local.s3.prefest.ru/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_function_set_id')) include '/var/www/local.s3.prefest.ru/app/functions/smarty_plugins/function.set_id.php';
?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {
$_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start();
$_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:meta")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:meta"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<?php if ($_smarty_tpl->tpl_vars['display_base_href']->value) {?>
<base href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['config']->value['current_location'], ENT_QUOTES, 'UTF-8');?>
/" />
<?php }?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo htmlspecialchars(@constant('CHARSET'), ENT_QUOTES, 'UTF-8');?>
" data-ca-mode="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['store_trigger']->value, ENT_QUOTES, 'UTF-8');?>
" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:meta_description")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:meta_description"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<meta name="description" content="<?php echo htmlspecialchars((($tmp = @html_entity_decode($_smarty_tpl->tpl_vars['meta_description']->value,@constant('ENT_COMPAT'),"UTF-8"))===null||$tmp==='' ? $_smarty_tpl->tpl_vars['location_data']->value['meta_description'] : $tmp), ENT_QUOTES, 'UTF-8');?>
" />
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:meta_description"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<meta name="keywords" content="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['meta_keywords']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['location_data']->value['meta_keywords'] : $tmp), ENT_QUOTES, 'UTF-8');?>
" />
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:meta"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<?php echo $_smarty_tpl->tpl_vars['location_data']->value['custom_html'];?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();
if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {
if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="meta.tpl" id="<?php echo smarty_function_set_id(array('name'=>"meta.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else {
echo Smarty::$_smarty_vars['capture']['template_content'];
}
}
} else {
$_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:meta")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:meta"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<?php if ($_smarty_tpl->tpl_vars['display_base_href']->value) {?>
<base href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['config']->value['current_location'], ENT_QUOTES, 'UTF-8');?>
/" />
<?php }?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo htmlspecialchars(@constant('CHARSET'), ENT_QUOTES, 'UTF-8');?>
" data-ca-mode="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['store_trigger']->value, ENT_QUOTES, 'UTF-8');?>
" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:meta_description")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:meta_description"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<meta name="description" content="<?php echo htmlspecialchars((($tmp = @html_entity_decode($_smarty_tpl->tpl_vars['meta_description']->value,@constant('ENT_COMPAT'),"UTF-8"))===null||$tmp==='' ? $_smarty_tpl->tpl_vars['location_data']->value['meta_description'] : $tmp), ENT_QUOTES, 'UTF-8');?>
" />
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:meta_description"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<meta name="keywords" content="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['meta_keywords']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['location_data']->value['meta_keywords'] : $tmp), ENT_QUOTES, 'UTF-8');?>
" />
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:meta"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<?php echo $_smarty_tpl->tpl_vars['location_data']->value['custom_html'];?>

<?php }?><?php }} ?>
