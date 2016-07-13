<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:26:40
         compiled from "/var/www/local.s3.prefest.ru/design/themes/responsive/templates/common/select_object.tpl" */ ?>
<?php /*%%SmartyHeaderCode:15699594057861760bb98d6-12952447%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0b66f96152c85c2872c91d4ea74dc33ff57e5b0d' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/themes/responsive/templates/common/select_object.tpl',
      1 => 1468405241,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '15699594057861760bb98d6-12952447',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'text' => 0,
    'style' => 0,
    'selected_id' => 0,
    'suffix' => 0,
    'display_icons' => 0,
    'items' => 0,
    'link_class' => 0,
    'key_name' => 0,
    'link_tpl' => 0,
    'id' => 0,
    'item' => 0,
    'var_name' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_57861760c8c121_42744442',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57861760c8c121_42744442')) {function content_57861760c8c121_42744442($_smarty_tpl) {?><?php if (!is_callable('smarty_function_set_id')) include '/var/www/local.s3.prefest.ru/app/functions/smarty_plugins/function.set_id.php';
?><?php
fn_preload_lang_vars(array('select_descr_lang','select_descr_lang'));
?>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {
$_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start();
$_smarty_tpl->tpl_vars["language_text"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['text']->value)===null||$tmp==='' ? $_smarty_tpl->__("select_descr_lang") : $tmp), null, 0);?>

<?php if ($_smarty_tpl->tpl_vars['style']->value=="graphic") {?>
    <?php if ($_smarty_tpl->tpl_vars['text']->value) {?><div class="ty-select-block__txt hidden-phone hidden-tablet"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['text']->value, ENT_QUOTES, 'UTF-8');?>
:</div><?php }?>
    
    <a class="ty-select-block__a cm-combination" id="sw_select_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['selected_id']->value, ENT_QUOTES, 'UTF-8');?>
_wrap_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
">
        <?php if ($_smarty_tpl->tpl_vars['display_icons']->value==true) {?>
            <i class="ty-select-block__a-flag ty-flag ty-flag-<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['items']->value[$_smarty_tpl->tpl_vars['selected_id']->value]['country_code'], 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
 cm-external-click" data-ca-external-click-id="sw_select_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['selected_id']->value, ENT_QUOTES, 'UTF-8');?>
_wrap_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
" ></i>
        <?php }?>
        <span class="ty-select-block__a-item <?php if ($_smarty_tpl->tpl_vars['link_class']->value) {
echo htmlspecialchars($_smarty_tpl->tpl_vars['link_class']->value, ENT_QUOTES, 'UTF-8');
}?>"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['items']->value[$_smarty_tpl->tpl_vars['selected_id']->value][$_smarty_tpl->tpl_vars['key_name']->value], ENT_QUOTES, 'UTF-8');
if ($_smarty_tpl->tpl_vars['items']->value[$_smarty_tpl->tpl_vars['selected_id']->value]['symbol']) {?> (<?php echo $_smarty_tpl->tpl_vars['items']->value[$_smarty_tpl->tpl_vars['selected_id']->value]['symbol'];?>
)<?php }?></span>
        <i class="ty-select-block__arrow ty-icon-down-micro"></i>
    </a>

    <div id="select_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['selected_id']->value, ENT_QUOTES, 'UTF-8');?>
_wrap_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
" class="ty-select-block cm-popup-box hidden">
        <ul class="cm-select-list ty-select-block__list ty-flags">
            <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['id'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['items']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['id']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
                <li class="ty-select-block__list-item">
                    <a rel="nofollow" href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['link_tpl']->value).((string)$_smarty_tpl->tpl_vars['id']->value)), ENT_QUOTES, 'UTF-8');?>
" class="ty-select-block__list-a <?php if ($_smarty_tpl->tpl_vars['selected_id']->value==$_smarty_tpl->tpl_vars['id']->value) {?>is-active<?php }?> <?php if ($_smarty_tpl->tpl_vars['suffix']->value=="live_editor_box") {?>cm-lang-link<?php }?>" <?php if ($_smarty_tpl->tpl_vars['display_icons']->value==true) {?>data-ca-country-code="<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['item']->value['country_code'], 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
"<?php }?> data-ca-name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">
                    <?php if ($_smarty_tpl->tpl_vars['display_icons']->value==true) {?>
                        <i class="ty-flag ty-flag-<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['item']->value['country_code'], 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
"></i>
                    <?php }?>
                    <?php echo $_smarty_tpl->tpl_vars['item']->value[$_smarty_tpl->tpl_vars['key_name']->value];
if ($_smarty_tpl->tpl_vars['item']->value['symbol']) {?> (<?php echo $_smarty_tpl->tpl_vars['item']->value['symbol'];?>
)<?php }?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } else { ?>
    <?php if ($_smarty_tpl->tpl_vars['text']->value) {?><label for="id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['var_name']->value, ENT_QUOTES, 'UTF-8');?>
" class="ty-select-block__txt hidden-phone hidden-tablet"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['text']->value, ENT_QUOTES, 'UTF-8');?>
:</label><?php }?>
    <select id="id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['var_name']->value, ENT_QUOTES, 'UTF-8');?>
" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['var_name']->value, ENT_QUOTES, 'UTF-8');?>
" onchange="Tygh.$.redirect(this.value);" class="ty-valign">
        <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['id'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['items']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['id']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
            <option value="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['link_tpl']->value).((string)$_smarty_tpl->tpl_vars['id']->value)), ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['id']->value==$_smarty_tpl->tpl_vars['selected_id']->value) {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value[$_smarty_tpl->tpl_vars['key_name']->value];?>
</option>
        <?php } ?>
    </select>
<?php }?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();
if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {
if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="common/select_object.tpl" id="<?php echo smarty_function_set_id(array('name'=>"common/select_object.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else {
echo Smarty::$_smarty_vars['capture']['template_content'];
}
}
} else {
$_smarty_tpl->tpl_vars["language_text"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['text']->value)===null||$tmp==='' ? $_smarty_tpl->__("select_descr_lang") : $tmp), null, 0);?>

<?php if ($_smarty_tpl->tpl_vars['style']->value=="graphic") {?>
    <?php if ($_smarty_tpl->tpl_vars['text']->value) {?><div class="ty-select-block__txt hidden-phone hidden-tablet"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['text']->value, ENT_QUOTES, 'UTF-8');?>
:</div><?php }?>
    
    <a class="ty-select-block__a cm-combination" id="sw_select_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['selected_id']->value, ENT_QUOTES, 'UTF-8');?>
_wrap_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
">
        <?php if ($_smarty_tpl->tpl_vars['display_icons']->value==true) {?>
            <i class="ty-select-block__a-flag ty-flag ty-flag-<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['items']->value[$_smarty_tpl->tpl_vars['selected_id']->value]['country_code'], 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
 cm-external-click" data-ca-external-click-id="sw_select_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['selected_id']->value, ENT_QUOTES, 'UTF-8');?>
_wrap_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
" ></i>
        <?php }?>
        <span class="ty-select-block__a-item <?php if ($_smarty_tpl->tpl_vars['link_class']->value) {
echo htmlspecialchars($_smarty_tpl->tpl_vars['link_class']->value, ENT_QUOTES, 'UTF-8');
}?>"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['items']->value[$_smarty_tpl->tpl_vars['selected_id']->value][$_smarty_tpl->tpl_vars['key_name']->value], ENT_QUOTES, 'UTF-8');
if ($_smarty_tpl->tpl_vars['items']->value[$_smarty_tpl->tpl_vars['selected_id']->value]['symbol']) {?> (<?php echo $_smarty_tpl->tpl_vars['items']->value[$_smarty_tpl->tpl_vars['selected_id']->value]['symbol'];?>
)<?php }?></span>
        <i class="ty-select-block__arrow ty-icon-down-micro"></i>
    </a>

    <div id="select_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['selected_id']->value, ENT_QUOTES, 'UTF-8');?>
_wrap_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
" class="ty-select-block cm-popup-box hidden">
        <ul class="cm-select-list ty-select-block__list ty-flags">
            <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['id'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['items']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['id']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
                <li class="ty-select-block__list-item">
                    <a rel="nofollow" href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['link_tpl']->value).((string)$_smarty_tpl->tpl_vars['id']->value)), ENT_QUOTES, 'UTF-8');?>
" class="ty-select-block__list-a <?php if ($_smarty_tpl->tpl_vars['selected_id']->value==$_smarty_tpl->tpl_vars['id']->value) {?>is-active<?php }?> <?php if ($_smarty_tpl->tpl_vars['suffix']->value=="live_editor_box") {?>cm-lang-link<?php }?>" <?php if ($_smarty_tpl->tpl_vars['display_icons']->value==true) {?>data-ca-country-code="<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['item']->value['country_code'], 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
"<?php }?> data-ca-name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">
                    <?php if ($_smarty_tpl->tpl_vars['display_icons']->value==true) {?>
                        <i class="ty-flag ty-flag-<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['item']->value['country_code'], 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
"></i>
                    <?php }?>
                    <?php echo $_smarty_tpl->tpl_vars['item']->value[$_smarty_tpl->tpl_vars['key_name']->value];
if ($_smarty_tpl->tpl_vars['item']->value['symbol']) {?> (<?php echo $_smarty_tpl->tpl_vars['item']->value['symbol'];?>
)<?php }?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } else { ?>
    <?php if ($_smarty_tpl->tpl_vars['text']->value) {?><label for="id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['var_name']->value, ENT_QUOTES, 'UTF-8');?>
" class="ty-select-block__txt hidden-phone hidden-tablet"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['text']->value, ENT_QUOTES, 'UTF-8');?>
:</label><?php }?>
    <select id="id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['var_name']->value, ENT_QUOTES, 'UTF-8');?>
" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['var_name']->value, ENT_QUOTES, 'UTF-8');?>
" onchange="Tygh.$.redirect(this.value);" class="ty-valign">
        <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['id'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['items']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['id']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
            <option value="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['link_tpl']->value).((string)$_smarty_tpl->tpl_vars['id']->value)), ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['id']->value==$_smarty_tpl->tpl_vars['selected_id']->value) {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value[$_smarty_tpl->tpl_vars['key_name']->value];?>
</option>
        <?php } ?>
    </select>
<?php }?>
<?php }?><?php }} ?>
