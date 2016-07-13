<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:26:48
         compiled from "/var/www/local.s3.prefest.ru/design/themes/responsive/templates/addons/rus_yandex_metrika/hooks/index/scripts.post.tpl" */ ?>
<?php /*%%SmartyHeaderCode:279023317578617684ee4f0-89568165%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'da88c5f6fe75db341e0e74cbd9425793b3ac0540' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/themes/responsive/templates/addons/rus_yandex_metrika/hooks/index/scripts.post.tpl',
      1 => 1468405566,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '279023317578617684ee4f0-89568165',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'yandex_metrika_goals_scheme' => 0,
    'addons' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_57861768579f45_53030271',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57861768579f45_53030271')) {function content_57861768579f45_53030271($_smarty_tpl) {?><?php if (!is_callable('smarty_function_script')) include '/var/www/local.s3.prefest.ru/app/functions/smarty_plugins/function.script.php';
if (!is_callable('smarty_function_set_id')) include '/var/www/local.s3.prefest.ru/app/functions/smarty_plugins/function.set_id.php';
?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {
$_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start(); ?><?php echo '<script'; ?>
 type="text/javascript">

    window.dataLayerYM = window.dataLayerYM || [];

    (function(_, $) {
        $.extend(_, {
            yandex_metrika: {
                goals_scheme: <?php echo json_encode($_smarty_tpl->tpl_vars['yandex_metrika_goals_scheme']->value);?>
,
                settings: {
                    id: <?php echo (($tmp = @$_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['counter_number'])===null||$tmp==='' ? "''" : $tmp);?>
,
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['clickmap']=='Y') {?> clickmap: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['external_links']=='Y') {?> trackLinks: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['denial']=='Y') {?> accurateTrackBounce: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['track_hash']=='Y') {?> trackHash: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['visor']=='Y') {?> webvisor: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['ecommerce']=='Y') {?> ecommerce:"dataLayerYM",<?php }?>
                    collect_stats_for_goals: <?php echo json_encode($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['collect_stats_for_goals']);?>
,
                },
                current_controller: '<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['runtime']->value['controller'], ENT_QUOTES, 'UTF-8');?>
',
                current_mode: '<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['runtime']->value['mode'], ENT_QUOTES, 'UTF-8');?>
'
            }
        });
    }(Tygh, Tygh.$));
<?php echo '</script'; ?>
>

<?php echo smarty_function_script(array('src'=>"js/addons/rus_yandex_metrika/func.js"),$_smarty_tpl);?>


<?php echo $_smarty_tpl->getSubTemplate ("addons/rus_yandex_metrika/views/components/datalayer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();
if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {
if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="addons/rus_yandex_metrika/hooks/index/scripts.post.tpl" id="<?php echo smarty_function_set_id(array('name'=>"addons/rus_yandex_metrika/hooks/index/scripts.post.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else {
echo Smarty::$_smarty_vars['capture']['template_content'];
}
}
} else { ?><?php echo '<script'; ?>
 type="text/javascript">

    window.dataLayerYM = window.dataLayerYM || [];

    (function(_, $) {
        $.extend(_, {
            yandex_metrika: {
                goals_scheme: <?php echo json_encode($_smarty_tpl->tpl_vars['yandex_metrika_goals_scheme']->value);?>
,
                settings: {
                    id: <?php echo (($tmp = @$_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['counter_number'])===null||$tmp==='' ? "''" : $tmp);?>
,
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['clickmap']=='Y') {?> clickmap: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['external_links']=='Y') {?> trackLinks: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['denial']=='Y') {?> accurateTrackBounce: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['track_hash']=='Y') {?> trackHash: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['visor']=='Y') {?> webvisor: true,<?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['ecommerce']=='Y') {?> ecommerce:"dataLayerYM",<?php }?>
                    collect_stats_for_goals: <?php echo json_encode($_smarty_tpl->tpl_vars['addons']->value['rus_yandex_metrika']['collect_stats_for_goals']);?>
,
                },
                current_controller: '<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['runtime']->value['controller'], ENT_QUOTES, 'UTF-8');?>
',
                current_mode: '<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['runtime']->value['mode'], ENT_QUOTES, 'UTF-8');?>
'
            }
        });
    }(Tygh, Tygh.$));
<?php echo '</script'; ?>
>

<?php echo smarty_function_script(array('src'=>"js/addons/rus_yandex_metrika/func.js"),$_smarty_tpl);?>


<?php echo $_smarty_tpl->getSubTemplate ("addons/rus_yandex_metrika/views/components/datalayer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php }?><?php }} ?>
