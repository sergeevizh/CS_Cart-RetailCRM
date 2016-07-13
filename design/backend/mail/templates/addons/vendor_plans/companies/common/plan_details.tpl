
<table>
    <tr>
        <td>{__("vendor_plans.plan")}:</td>
        <td>{$plan->plan}</td>
    </tr>
    <tr>
        <td>{__("price")} ({$currencies.$primary_currency.symbol}):</td>
        <td>{include file="common/price.tpl" value=$plan->price}&nbsp;/&nbsp;{__("vendor_plans.{$plan.periodicity}")|lower}</td>
    </tr>
    <tr>
        <td>{__("vendor_plans.products_limit")}:</td>
        <td>{if $plan->products_limit}{$plan->products_limit}{else}{__("vendor_plans.unlimited")}{/if}</td>
    </tr>
    <tr>
        <td>{__("vendor_plans.revenue_up_to")} ({$currencies.$primary_currency.symbol}):</td>
        <td>{if $plan->revenue_limit|floatval}{include file="common/price.tpl" value=$plan->revenue_limit}{else}{__("vendor_plans.unlimited")}{/if}</td>
    </tr>
    <tr>
        <td>{__("vendor_plans.transaction_fee")}:</td>
        <td>{$plan->commission|floatval}(%)</td>
    </tr>
    <tr>
        <td>{__("vendor_plans.vendor_store")}:</td>
        <td>{if $plan->vendor_store}{__("yes")}{else}{__("no")}{/if}</td>
    </tr>
    {if $plan->description}
    <tr>
        <td>{__("description")}:</td>
        <td>{$plan->description}</td>
    </tr>
    {/if}
</table>
