{if $addons.rus_yandex_metrika.ecommerce == 'Y'}
    <script type="text/javascript">
        (function(w, _, $) {
            $(document).ready(function(){
                w.dataLayerYM.push({
                    "ecommerce": {
                        "detail": {
                            "products": [
                                {
                                    "id": {$product.product_id},
                                    "name" : "{$product.product nofilter}",
                                    "price": "{$product.price}",
                                    "brand": "{$ym_brand}",
                                    {if $ym_variant}
                                    "variant": "{$ym_variant}",
                                    {/if}
                                    {if $category}
                                    "category": "{$category}",
                                    {/if}
                                }
                            ]
                        }
                    }
                });
            });
        }(window, Tygh, Tygh.$));
    </script>
{/if}