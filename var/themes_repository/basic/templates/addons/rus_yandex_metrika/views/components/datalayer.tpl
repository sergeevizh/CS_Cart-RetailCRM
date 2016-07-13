{if $addons.rus_yandex_metrika.ecommerce == 'Y'}
    <script type="text/javascript">
        (function(w, _, $) {
            $(document).ready(function(){

                {if !empty($yandex_metrika.deleted)}
                w.dataLayerYM.push({
                    'ecommerce': {
                        'remove': {
                            'products': [
                                {foreach from=$yandex_metrika.deleted item='product'}
                                {
                                    'id': {$product.id},
                                    'name': '{$product.name nofilter}',
                                    'quantity': {$product.quantity},
                                    {if $product.category}
                                    'category': '{$product.category}',
                                    {/if}
                                },
                                {/foreach}
                            ]
                        }
                    }
                });
                {/if}

                {if !empty($yandex_metrika.added)}
                w.dataLayerYM.push({
                    'ecommerce': {
                        'add': {
                            'products': [
                                {foreach from=$yandex_metrika.added item='product'}
                                {
                                    'id': {$product.id},
                                    'name' : '{$product.name nofilter}',
                                    'price': {$product.price},
                                    'quantity': {$product.quantity},
                                    {if $product.brand}
                                    'brand': '{$product.brand}',
                                    {/if}
                                    {if $product.category}
                                    'category': '{$product.category}',
                                    {/if}
                                },
                                {/foreach}
                            ]
                        }
                    }
                });
                {/if}

                {if !empty($yandex_metrika.purchased)}
                w.dataLayerYM.push({
                    'ecommerce': {
                        'purchase': {
                            'actionField' : {
                                'id' : {$yandex_metrika.purchased.action.id},
                                'revenue' : {$yandex_metrika.purchased.action.revenue},
                                {if $yandex_metrika.purchased.action.coupon}
                                'coupon' : '{$yandex_metrika.purchased.action.coupon}'
                                {/if}
                            },
                            'products': [
                                {foreach from=$yandex_metrika.purchased.products item='product'}
                                {
                                    'id': {$product.id},
                                    'name' : '{$product.name nofilter}',
                                    'price': {$product.price},
                                    {if $product.brand}
                                    'brand': '{$product.brand}',
                                    {/if}
                                    {if $product.category}
                                    'category': '{$product.category}',
                                    {/if}
                                    'quantity': {$product.quantity},
                                },
                                {/foreach}
                            ]
                        }
                    }
                });
                {/if}
            });
        }(window, Tygh, Tygh.$));
    </script>
{/if}