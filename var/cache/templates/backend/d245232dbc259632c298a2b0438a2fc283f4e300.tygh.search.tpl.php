<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:26:28
         compiled from "/var/www/local.s3.prefest.ru/design/backend/templates/buttons/search.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1983967096578617544f4ee6-86143038%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd245232dbc259632c298a2b0438a2fc283f4e300' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/backend/templates/buttons/search.tpl',
      1 => 1466664541,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1983967096578617544f4ee6-86143038',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'but_onclick' => 0,
    'but_href' => 0,
    'but_role' => 0,
    'but_name' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_578617545001f9_32273891',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_578617545001f9_32273891')) {function content_578617545001f9_32273891($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('search'));
?>

<?php echo $_smarty_tpl->getSubTemplate ("buttons/button.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_text'=>__("search"),'but_onclick'=>$_smarty_tpl->tpl_vars['but_onclick']->value,'but_href'=>$_smarty_tpl->tpl_vars['but_href']->value,'but_role'=>$_smarty_tpl->tpl_vars['but_role']->value,'but_name'=>$_smarty_tpl->tpl_vars['but_name']->value), 0);?>
<?php }} ?>
