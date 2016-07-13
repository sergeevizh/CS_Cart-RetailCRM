<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:26:28
         compiled from "/var/www/local.s3.prefest.ru/design/backend/templates/common/saved_search.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2108543918578617541ba182-70231678%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '09bc28f9c18817c3999418867835cd8ccbc478d3' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/backend/templates/common/saved_search.tpl',
      1 => 1466664541,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '2108543918578617541ba182-70231678',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'view_type' => 0,
    'views' => 0,
    'search' => 0,
    'dispatch' => 0,
    'view_suffix' => 0,
    'max_items' => 0,
    's_id' => 0,
    'view' => 0,
    'config' => 0,
    'redirect_current_url' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_5786175421ec40_58697361',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5786175421ec40_58697361')) {function content_5786175421ec40_58697361($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/local.s3.prefest.ru/app/functions/smarty_plugins/block.hook.php';
?><?php
fn_preload_lang_vars(array('saved_search','all','more','more','delete','new_saved_search'));
?>
<?php $_smarty_tpl->tpl_vars["views"] = new Smarty_variable(fn_get_views($_smarty_tpl->tpl_vars['view_type']->value), null, 0);?>

<?php $_smarty_tpl->tpl_vars["max_items"] = new Smarty_variable("4", null, 0);?>
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"advanced_search:views")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"advanced_search:views"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

    <?php if ($_smarty_tpl->tpl_vars['views']->value) {?>
        <div class="sidebar-row" id="views">
            <h6><?php echo $_smarty_tpl->__("saved_search");?>
</h6>
                <ul class="nav nav-list saved-search">
                    <?php if ($_smarty_tpl->tpl_vars['views']->value) {?>
                    <li <?php if (!$_smarty_tpl->tpl_vars['search']->value['view_id']) {?>class="active"<?php }?>>
                        <a href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['dispatch']->value).".reset_view?".((string)$_smarty_tpl->tpl_vars['view_suffix']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("all");?>
</a>
                    </li>
                    <?php  $_smarty_tpl->tpl_vars['view'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['view']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['views']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['view']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['views']['total'] = $_smarty_tpl->tpl_vars['view']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['views']['index']=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['view']->key => $_smarty_tpl->tpl_vars['view']->value) {
$_smarty_tpl->tpl_vars['view']->_loop = true;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['views']['index']++;
?>
                        <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['views']['index']==$_smarty_tpl->tpl_vars['max_items']->value) {?>
                        <?php $_smarty_tpl->tpl_vars['s_id'] = new Smarty_variable(sprintf("saved_searches_%s",fn_crc32($_smarty_tpl->tpl_vars['dispatch']->value)), null, 0);?>
                        <li>
                            <span class="more hand">
                                <a id="on_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['s_id']->value, ENT_QUOTES, 'UTF-8');?>
" class="collapsed cm-combination cm-save-state <?php if ($_COOKIE[$_smarty_tpl->tpl_vars['s_id']->value]) {?>hidden<?php }?>"><?php echo $_smarty_tpl->__("more");?>
<i class="exicon-collapse"></i></a>
                                <a id="off_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['s_id']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-combination cm-save-state <?php if (!$_COOKIE[$_smarty_tpl->tpl_vars['s_id']->value]) {?>hidden<?php }?>"><?php echo $_smarty_tpl->__("more");?>
<i class="exicon-collapse"></i></a>
                            </span>
                        </li>
                        <li id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['s_id']->value, ENT_QUOTES, 'UTF-8');?>
" class="<?php if (!$_COOKIE[$_smarty_tpl->tpl_vars['s_id']->value]) {?>hidden<?php }?>">
                            <ul class="nav nav-list">
                        <?php }?>
                        <li <?php if ($_smarty_tpl->tpl_vars['view']->value['view_id']==$_smarty_tpl->tpl_vars['search']->value['view_id']) {?>class="active"<?php }?>>
                            <?php $_smarty_tpl->tpl_vars["return_current_url"] = new Smarty_variable(fn_query_remove($_smarty_tpl->tpl_vars['config']->value['current_url'],"view_id","new_view"), null, 0);?>
                            <?php $_smarty_tpl->tpl_vars["redirect_current_url"] = new Smarty_variable(rawurlencode($_smarty_tpl->tpl_vars['config']->value['current_url']), null, 0);?>
                            <a href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['dispatch']->value).".delete_view?view_id=".((string)$_smarty_tpl->tpl_vars['view']->value['view_id'])."&redirect_url=".((string)$_smarty_tpl->tpl_vars['redirect_current_url']->value)), ENT_QUOTES, 'UTF-8');?>
" class="cm-confirm cm-tooltip icon-trash" title="<?php echo $_smarty_tpl->__("delete");?>
"></a>
                            <a class="cm-view-name" data-ca-view-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['view']->value['view_id'], ENT_QUOTES, 'UTF-8');?>
" href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['dispatch']->value)."?view_id=".((string)$_smarty_tpl->tpl_vars['view']->value['view_id'])."&".((string)$_smarty_tpl->tpl_vars['view_suffix']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['view']->value['name'], ENT_QUOTES, 'UTF-8');?>
</a>
                        </li>
                    <?php } ?>
                    <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['views']['total']>$_smarty_tpl->tpl_vars['max_items']->value) {?>
                            </ul>
                        </li>
                    <?php }?>
                    <?php }?>
                    <li class="last">
                        <?php echo $_smarty_tpl->getSubTemplate ("buttons/button.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_text'=>__("new_saved_search"),'but_role'=>"text",'but_meta'=>"text-button cm-dialog-opener",'but_target_id'=>"adv_search"), 0);?>

                    </li>
                </ul>
        </div>
        <hr>
    <?php }?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"advanced_search:views"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>
<?php }} ?>
