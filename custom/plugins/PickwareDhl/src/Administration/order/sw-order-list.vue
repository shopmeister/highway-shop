<template>
    {% block sw_order_list_grid_columns_actions_view %}
    {% parent %}

    <sw-context-menu-item
        class="sw-order-list__order-view-action"
        :routerLink="{ name: 'sw.order.detail.pw.shipping', params: { id: item.id, showShipmentRequestModal: true } }"
    >
        {{ $t('pickware-shipping-bundle-order-list.create-label') }}
    </sw-context-menu-item>
    {% endblock %}
</template>

<script>
export default {
    overrideFrom: 'sw-order-list',
};
</script>

<i18n>
    {
        "de-DE": {
            "pickware-shipping-bundle-order-list": {
                "create-label": "Versandetikett erstellen"
            }
        },
        "en-GB": {
            "pickware-shipping-bundle-order-list": {
                "create-label": "Create shipping label"
            }
        }
    }
</i18n>
