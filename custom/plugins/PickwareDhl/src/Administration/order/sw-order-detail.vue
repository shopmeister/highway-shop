<template>
    <!-- Use the block sw_order_detail_content_tabs_documents here instead of sw_order_detail_content_tabs_extension
        because SwagCommerical overwrites the block sw_order_detail_content_tabs_extension without calling the parent
        resulting in our tab items missing when SwagCommerical is active.
        If this is fixed in SwagCommerical we want to go back to sw_order_detail_content_tabs_extension.
        Further information: https://github.com/pickware/shopware-plugins/issues/4288 -->
    {% block sw_order_detail_content_tabs_documents %}
    {% parent %}
    <sw-tabs-item
        class="sw-order-detail__tabs-tab-shipping-labels"
        :class="{ 'has-warning': isOrderEditing }"
        :title="$t('sw-order-detail.pickware-shipping-bundle.shipping-labels')"
        :route="{ name: 'sw.order.detail.pw.shipping', params: { id: $route.params.id } }"
    >
        {{ $t('sw-order-detail.pickware-shipping-bundle.shipping-labels') }} ({{ getShipmentsCount }})
    </sw-tabs-item>
    {% endblock %}
</template>

<script>
import { Component, State } from '@pickware/shopware-adapter';

import { ShipmentListStore, ShipmentListStoreNamespace } from '../shipment/shipment-list-store.js';

const { mapGetters, mapActions } = Component.getComponentHelper();
export default {
    overrideFrom: 'sw-order-detail',

    computed: {
        ...mapGetters(ShipmentListStoreNamespace, ['getShipmentsCount']),
    },

    beforeCreate() {
        State.registerModule(ShipmentListStoreNamespace, ShipmentListStore);
    },

    beforeUnmount() {
        State.unregisterModule(ShipmentListStoreNamespace);
    },

    methods: {
        ...mapActions(ShipmentListStoreNamespace, ['setShipmentsForOrder']),

        createNewVersionId() {
            this.setShipmentsForOrder(this.orderId);

            return this.$super('createNewVersionId');
        },
    },

};
</script>

<i18n>
{
    "de-DE": {
        "sw-order-detail": {
            "pickware-shipping-bundle": {
                "shipping-labels": "Versandetiketten"
            }
        }
    },
    "en-GB": {
        "sw-order-detail": {
            "pickware-shipping-bundle": {
                "shipping-labels": "Shipping labels"
            }
        }
    }
}
</i18n>
