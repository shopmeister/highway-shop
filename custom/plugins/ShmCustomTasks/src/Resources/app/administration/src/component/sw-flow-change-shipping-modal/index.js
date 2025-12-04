import template from './sw-flow-change-shipping-modal.html.twig';

const { Component } = Shopware;
const { EntityCollection, Context } = Shopware.Data;

export default Component.register('sw-flow-change-shipping-modal', {
    template,

    inject: [
        'repositoryFactory',
    ],

    props: {
        sequence: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            shippingMethod: [],
            amount: 1.0,
            selectedCondition: '',
            actionIsActive: false,
            conditions: [
                {
                    key: '=',
                    label: 'change-shipping-action.conditions.equals',
                },
                {
                    key: '>',
                    label: 'change-shipping-action.conditions.greaterThan',
                },
                {
                    key: '>=',
                    label: 'change-shipping-action.conditions.greaterThanOrEquals',
                },
                {
                    key: '<',
                    label: 'change-shipping-action.conditions.lessThan',
                },
                {
                    key: '<=',
                    label: 'change-shipping-action.conditions.lessThanOrEquals',
                },
            ]
        };
    },

    computed: {
        shippingRepository() {
            return this.repositoryFactory.create('shipping_method');
        },

        shippingMethodId() {
            if (!this.shippingMethod.length) {
                return null;
            }
            return this.shippingMethod[0].id;
        },

        weightAmount() {
            return this.amount;
        },

        conditionOptions() {
            return this.conditions;
        }
    },

    watch: {
        'sequence.config': {
            handler(config) {
                if (config?.shippingMethod?.length > 0) {
                    if (config.shippingMethod) {
                        this.shippingMethod = config.shippingMethod;
                    }
                } else {
                    this.shippingMethod = this.createShippingMethodCollection();
                }
                if (config?.isActive) {
                    this.actionIsActive = config.isActive;
                }
            },
            immediate: true
        }
    },

    methods: {
        createShippingMethodCollection() {
            return new EntityCollection(
                this.shippingRepository.route,
                this.shippingRepository.entityName,
                Context.api,
            );
        },

        selectedShippingMethod(id, item) {
            const collection = this.createShippingMethodCollection();

            if (item) {
                collection.add(item);
            }

            this.shippingMethod = collection;
        },

        onChangeAction(value) {
            this.actionIsActive = value;
        },

        onConditionChange(value) {
            this.selectedCondition = value;
        },

        onChangedWeightAmount(value) {
            this.amount = value;
        },

        onAddAction() {
            const data = {
                ...this.sequence,
                config: {
                    ...this.sequence.config,
                    shippingMethod: this.shippingMethod,
                    condition: this.selectedCondition,
                    weightAmount: this.amount,
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
