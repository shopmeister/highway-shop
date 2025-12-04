import template from './sw-flow-create-return-label-modal.html.twig';

const { Component } = Shopware;
const { EntityCollection, Context } = Shopware.Data;

export default Component.register('sw-flow-create-return-label-modal', {
    template,

    inject: [
        'repositoryFactory',
    ],

    props: {
        actionIsActive: {
            type: Boolean,
            default: false,
            required: true,
        },
        sequence: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            shippingCarrierMethod: [],
            actionIsActive: this.actionIsActive
        };
    },

    computed: {
        shippingCarrierRepository() {
            return this.repositoryFactory.create('pickware_shipping_carrier');
        },

        shippingCarrierId() {
            if (!this.shippingCarrierMethod.length) {
                return null;
            }
            return this.shippingCarrierMethod[0].id;
        }
    },

    watch: {
        'sequence.config': {
            handler(config) {
                if (config?.shippingCarrierMethod?.length > 0) {
                    if (config.shippingCarrierMethod) {
                        this.shippingCarrierMethod = config.shippingCarrierMethod;
                    }
                } else {
                    this.shippingCarrierMethod = this.createShippingCarrierCollection();
                }
                if (config?.isActive) {
                    this.actionIsActive = config.isActive;
                }
            },
            immediate: true
        }
    },

    methods: {
        createShippingCarrierCollection() {
            return new EntityCollection(
                this.shippingCarrierRepository.route,
                this.shippingCarrierRepository.entityName,
                Context.api,
            );
        },

        selectedShippingCarrierMethod(id, item) {
            const collection = this.createShippingCarrierCollection();

            if (item) {
                collection.add(item);
            }

            this.shippingCarrierMethod = collection;
        },

        onChangeAction(value) {
            this.actionIsActive = value;
        },

        onAddAction() {
            const isActive = this.actionIsActive;

            const data = {
                ...this.sequence,
                config: {
                    ...this.sequence.config,
                    isActive,
                    shippingCarrierMethod: this.shippingCarrierMethod,
                },
            };

            this.$nextTick(() => {
                this.$emit('process-finish', data);
            });
        },

        onClose() {
            this.$emit('modal-close');
        }
    }
});
