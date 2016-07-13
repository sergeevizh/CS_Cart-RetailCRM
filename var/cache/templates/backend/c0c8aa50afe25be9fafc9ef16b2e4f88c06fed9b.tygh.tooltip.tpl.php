<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:22:03
         compiled from "/var/www/local.s3.prefest.ru/design/backend/templates/common/tooltip.tpl" */ ?>
<?php /*%%SmartyHeaderCode:10737043825786164b123d46-37164415%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c0c8aa50afe25be9fafc9ef16b2e4f88c06fed9b' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/backend/templates/common/tooltip.tpl',
      1 => 1466664541,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '10737043825786164b123d46-37164415',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'tooltip' => 0,
    'params' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_5786164b1348e3_92783861',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5786164b1348e3_92783861')) {function content_5786164b1348e3_92783861($_smarty_tpl) {?>&nbsp;<?php if ($_smarty_tpl->tpl_vars['tooltip']->value) {?><a class="cm-tooltip<?php if ($_smarty_tpl->tpl_vars['params']->value) {?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['params']->value, ENT_QUOTES, 'UTF-8');
}?>" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['tooltip']->value, ENT_QUOTES, 'UTF-8');?>
"><i class="icon-question-sign"></i></a><?php }?><?php }} ?>
