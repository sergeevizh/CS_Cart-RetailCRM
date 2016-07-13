{$type = $field.field_name|substr: 2}

<input type="text" style="display:none;" autocomplete="on | off" />

{if $type == 'city'}
    {script src="js/addons/rus_cities/func.js"}
{/if}
