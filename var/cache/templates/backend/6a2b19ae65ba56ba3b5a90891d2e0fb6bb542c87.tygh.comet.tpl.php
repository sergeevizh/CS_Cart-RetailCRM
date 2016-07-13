<?php /* Smarty version Smarty-3.1.21, created on 2016-07-13 13:20:58
         compiled from "/var/www/local.s3.prefest.ru/design/backend/templates/common/comet.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1747734715786160ab58226-92742314%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6a2b19ae65ba56ba3b5a90891d2e0fb6bb542c87' => 
    array (
      0 => '/var/www/local.s3.prefest.ru/design/backend/templates/common/comet.tpl',
      1 => 1466664541,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1747734715786160ab58226-92742314',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.21',
  'unifunc' => 'content_5786160ab609d8_96119169',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5786160ab609d8_96119169')) {function content_5786160ab609d8_96119169($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('processing'));
?>
<a id="comet_container_controller" data-backdrop="static" data-keyboard="false" href="#comet_control" data-toggle="modal" class="hide"></a>

<div class="modal hide fade" id="comet_control" tabindex="-1" role="dialog" aria-labelledby="comet_title" aria-hidden="true">
    <div class="modal-header">
        <h3 id="comet_title"><?php echo $_smarty_tpl->__("processing");?>
</h3>
    </div>
    <div class="modal-body">
        <p></p>
        <div class="progress progress-striped active">
            
            <div class="bar" style="width: 0%;"></div>
        </div>
    </div>
</div><?php }} ?>
