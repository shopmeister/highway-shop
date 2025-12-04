<template>
    {% block sw_settings_shipping_detail_base %}
    {% parent %}

    <pw-shipping-shipping-method-settings-card
        v-if="shippingMethod && shippingMethod.id"
        ref="pwShippingShippingMethodSettingsCard"
        :shippingMethodId="shippingMethod.id"
    />
    {% endblock %}
</template>

<script>
import { Component, State } from '@pickware/shopware-adapter';
import { createErrorNotification } from '@pickware/shopware-administration-notification';

import { ShippingMethodConfigStore, ShippingMethodConfigStoreNamespace } from './shipping-method-config-store.js';

const { mapActions, mapGetters, mapMutations } = Component.getComponentHelper();

export default {
    overrideFrom: 'sw-settings-shipping-detail',

    data() {
        return {
            pwShippingActionAfterSave: null,
        };
    },

    computed: {
        ...mapGetters(ShippingMethodConfigStoreNamespace, {
            pwShippingAreDefaultBoxDimensionsValid: 'areDefaultBoxDimensionsValid',
        }),
    },

    beforeCreate() {
        State.registerModule(ShippingMethodConfigStoreNamespace, ShippingMethodConfigStore);
    },

    beforeUnmount() {
        State.unregisterModule(ShippingMethodConfigStoreNamespace);
    },

    methods: {
        ...mapActions(ShippingMethodConfigStoreNamespace, {
            pwShippingSaveShippingMethodConfig: 'saveShippingMethodConfig',
        }),
        ...mapMutations(ShippingMethodConfigStoreNamespace, {
            pwShippingSetShowBoxDimensionValidationResult: 'setShowBoxDimensionValidationResult',
        }),

        onSave() {
            this.pwShippingSetShowBoxDimensionValidationResult(true);
            if (!this.pwShippingAreDefaultBoxDimensionsValid) {
                createErrorNotification(
                    'sw-settings-index-shipping-configuration.pw-shipping-bundle.error.title',
                    'sw-settings-index-shipping-configuration.pw-shipping-bundle.error.message',
                );

                return new Promise((resolve) => {
                    resolve();
                });
            }

            // This method might be called when creating a new shipping method. In this case it is impossible to save
            // our settings before shopware has created and saved the new shipping method. Since the onSave method
            // is not async, we can't use await here. Instead we us then to call the save method after the shopware.
            const promise = this.$super('onSave');
            // eslint-disable-next-line promise/catch-or-return,promise/prefer-await-to-then
            promise.then(async () => await this.pwShippingSaveShippingMethodConfig());

            return promise;
        },
    },
};
</script>

<i18n>
{
    "de-DE": {
        "sw-settings-index-shipping-configuration": {
            "pw-shipping-bundle": {
                "error": {
                    "title": "Fehler",
                    "message": "Die Standard-Größeneinheiten von Paketen müssen entweder komplett ausgefüllt sein oder gelöscht werden."
                }
            }
        }
    },
    "en-GB": {
        "sw-settings-index-shipping-configuration": {
            "pw-shipping-bundle": {
                "error": {
                    "title": "Error",
                    "message": "The default size units of packages must either be completely filled out or deleted."
                }
            }
        }
    }
}
</i18n>
