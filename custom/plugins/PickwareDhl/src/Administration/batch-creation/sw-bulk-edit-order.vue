<template>
    {% block sw_bulk_edit_order_actions_save %}
    <sw-button-process
        variant="primary"
        :processSuccess="false"
        :isLoading="isLoading"
        :disabled="isLoading || selectedIds.length === 0"
        @click="pwShippingShowBatchLabelCreationModal = true"
    >
        {{ $t('sw-bulk-edit-order.pw-shipping.button') }}
    </sw-button-process>
    {% parent %}

    <pw-shipping-batch-creation-modal
        v-show="pwShippingShowBatchLabelCreationModal"
        :orderIds="selectedIds"
        @close="pwShippingShowBatchLabelCreationModal = false"
    />
    {% endblock %}
</template>

<script>
export default {
    overrideFrom: 'sw-bulk-edit-order',

    data() {
        return {
            pwShippingShowBatchLabelCreationModal: false,
        };
    },
};
</script>

<i18n>
{
    "de-DE": {
        "sw-bulk-edit-order": {
            "pw-shipping": {
                "button": "Versandetiketten erstellen"
            }
        }
    },
    "en-GB": {
        "sw-bulk-edit-order": {
            "pw-shipping": {
                "button": "Create shipping labels"
            }
        }
    }
}
</i18n>
