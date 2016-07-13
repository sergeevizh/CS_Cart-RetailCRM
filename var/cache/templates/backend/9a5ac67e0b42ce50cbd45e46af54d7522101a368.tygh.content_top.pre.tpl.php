<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:20:57
         compiled from "/var/www/local.s3.prefest.ru/design/backend/templates/addons/help_tutorial/hooks/index/content_top.pre.tpl" */ ?>
<?php /*%%SmartyHeaderCode:18889694605786160941ae78-49105209%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9a5ac67e0b42ce50cbd45e46af54d7522101a368' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/backend/templates/addons/help_tutorial/hooks/index/content_top.pre.tpl',
      1 => 1466664541,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '18889694605786160941ae78-49105209',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_5786160949da65_19742205',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5786160949da65_19742205')) {function content_5786160949da65_19742205($_smarty_tpl) {?><?php if (($_smarty_tpl->tpl_vars['runtime']->value['controller']=="block_manager"&&$_smarty_tpl->tpl_vars['runtime']->value['mode']=="manage")) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("addons/help_tutorial/components/video.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('count'=>"2",'item'=>"Tv7AZhmLwkw",'item2'=>"RseUfuFdctg",'open'=>false), 0);?>

<?php } elseif (($_smarty_tpl->tpl_vars['runtime']->value['controller']=="themes"&&$_smarty_tpl->tpl_vars['runtime']->value['mode']=="manage")) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("addons/help_tutorial/components/video.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('item'=>"BVOLfcROTyg",'open'=>false), 0);?>

<?php } elseif (($_smarty_tpl->tpl_vars['runtime']->value['controller']=="store_import"&&$_smarty_tpl->tpl_vars['runtime']->value['mode']=="index")) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("addons/help_tutorial/components/video.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('item'=>"cCJOoAZnCqk",'open'=>false), 0);?>

<?php } elseif ((fn_allowed_for("ULTIMATE")&&$_smarty_tpl->tpl_vars['runtime']->value['controller']=="companies")) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("addons/help_tutorial/components/video.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('item'=>"eUam0Puui3M",'open'=>fn_allowed_for("ULTIMATE:FREE")&&$_smarty_tpl->tpl_vars['runtime']->value['mode']=='manage'), 0);?>

<?php } elseif (($_smarty_tpl->tpl_vars['runtime']->value['controller']=="index"&&$_smarty_tpl->tpl_vars['runtime']->value['mode']=="index")) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("addons/help_tutorial/components/video.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('item'=>"5STIqzsPU9c",'open'=>false), 0);?>

<?php } elseif (($_smarty_tpl->tpl_vars['runtime']->value['controller']=="seo_rules"&&$_smarty_tpl->tpl_vars['runtime']->value['mode']=="manage")) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("addons/help_tutorial/components/video.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('item'=>"JUFXyew6lig",'open'=>false), 0);?>

<?php }?><?php }} ?>
