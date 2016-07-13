<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:26:46
         compiled from "/var/www/local.s3.prefest.ru/design/themes/responsive/templates/addons/rus_theme_style/overrides/addons/blog/blocks/text_links.tpl" */ ?>
<?php /*%%SmartyHeaderCode:141355024257861766a751a5-38710242%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ff27fde4c09aceb41d60378590a2a47ea38974e5' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/themes/responsive/templates/addons/rus_theme_style/overrides/addons/blog/blocks/text_links.tpl',
      1 => 1468405252,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '141355024257861766a751a5-38710242',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'items' => 0,
    'page' => 0,
    'settings' => 0,
    'parent_id' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_57861766adbd79_10201176',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57861766adbd79_10201176')) {function content_57861766adbd79_10201176($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_date_format')) include '/var/www/local.s3.prefest.ru/app/functions/smarty_plugins/modifier.date_format.php';
if (!is_callable('smarty_function_set_id')) include '/var/www/local.s3.prefest.ru/app/functions/smarty_plugins/function.set_id.php';
?><?php
fn_preload_lang_vars(array('view_all','view_all'));
?>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {
$_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start(); ?>

<?php if ($_smarty_tpl->tpl_vars['items']->value) {?>
    <div class="ty-blog-text-links">
        <ul>
            <?php  $_smarty_tpl->tpl_vars["page"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["page"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['items']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars["page"]->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars["page"]->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars["page"]->key => $_smarty_tpl->tpl_vars["page"]->value) {
$_smarty_tpl->tpl_vars["page"]->_loop = true;
 $_smarty_tpl->tpl_vars["page"]->iteration++;
 $_smarty_tpl->tpl_vars["page"]->last = $_smarty_tpl->tpl_vars["page"]->iteration === $_smarty_tpl->tpl_vars["page"]->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fe_blog"]['last'] = $_smarty_tpl->tpl_vars["page"]->last;
?>
                <li class="ty-blog-text-links__item">
                    <div class="ty-blog-text-links__date"><?php echo htmlspecialchars(smarty_modifier_date_format($_smarty_tpl->tpl_vars['page']->value['timestamp'],$_smarty_tpl->tpl_vars['settings']->value['Appearance']['date_format']), ENT_QUOTES, 'UTF-8');?>
</div>
                    <a href="<?php echo htmlspecialchars(fn_url("pages.view?page_id=".((string)$_smarty_tpl->tpl_vars['page']->value['page_id'])), ENT_QUOTES, 'UTF-8');?>
" class="ty-blog-text-links__a"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['page']->value['page'], ENT_QUOTES, 'UTF-8');?>
</a>
                    <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['fe_blog']['last']) {?>
                        <?php $_smarty_tpl->tpl_vars['parent_id'] = new Smarty_variable($_smarty_tpl->tpl_vars['page']->value['parent_id'], null, 0);?>
                    <?php }?>
                </li>
            <?php } ?>
        </ul>

        <div class="ty-mtb-s ty-uppercase">
            <a href="<?php echo htmlspecialchars(fn_url("pages.view?page_id=".((string)$_smarty_tpl->tpl_vars['parent_id']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("view_all");?>
</a>
        </div>
    </div>
<?php }
list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();
if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {
if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="/var/www/local.s3.prefest.ru/design/themes/responsive/templates/addons/rus_theme_style/overrides/addons/blog/blocks/text_links.tpl" id="<?php echo smarty_function_set_id(array('name'=>"/var/www/local.s3.prefest.ru/design/themes/responsive/templates/addons/rus_theme_style/overrides/addons/blog/blocks/text_links.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else {
echo Smarty::$_smarty_vars['capture']['template_content'];
}
}
} else { ?>

<?php if ($_smarty_tpl->tpl_vars['items']->value) {?>
    <div class="ty-blog-text-links">
        <ul>
            <?php  $_smarty_tpl->tpl_vars["page"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["page"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['items']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars["page"]->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars["page"]->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars["page"]->key => $_smarty_tpl->tpl_vars["page"]->value) {
$_smarty_tpl->tpl_vars["page"]->_loop = true;
 $_smarty_tpl->tpl_vars["page"]->iteration++;
 $_smarty_tpl->tpl_vars["page"]->last = $_smarty_tpl->tpl_vars["page"]->iteration === $_smarty_tpl->tpl_vars["page"]->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fe_blog"]['last'] = $_smarty_tpl->tpl_vars["page"]->last;
?>
                <li class="ty-blog-text-links__item">
                    <div class="ty-blog-text-links__date"><?php echo htmlspecialchars(smarty_modifier_date_format($_smarty_tpl->tpl_vars['page']->value['timestamp'],$_smarty_tpl->tpl_vars['settings']->value['Appearance']['date_format']), ENT_QUOTES, 'UTF-8');?>
</div>
                    <a href="<?php echo htmlspecialchars(fn_url("pages.view?page_id=".((string)$_smarty_tpl->tpl_vars['page']->value['page_id'])), ENT_QUOTES, 'UTF-8');?>
" class="ty-blog-text-links__a"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['page']->value['page'], ENT_QUOTES, 'UTF-8');?>
</a>
                    <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['fe_blog']['last']) {?>
                        <?php $_smarty_tpl->tpl_vars['parent_id'] = new Smarty_variable($_smarty_tpl->tpl_vars['page']->value['parent_id'], null, 0);?>
                    <?php }?>
                </li>
            <?php } ?>
        </ul>

        <div class="ty-mtb-s ty-uppercase">
            <a href="<?php echo htmlspecialchars(fn_url("pages.view?page_id=".((string)$_smarty_tpl->tpl_vars['parent_id']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("view_all");?>
</a>
        </div>
    </div>
<?php }
}?><?php }} ?>
