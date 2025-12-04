import {ACTION, GROUP} from '../../constant/shm-flow.constant';

const {Component} = Shopware;

Component.override('sw-flow-sequence-action', {

    computed: {
        modalName() {
            switch (this.selectedAction) {
                case ACTION.CHANGE_SHIPPING:
                    return 'sw-flow-change-shipping-modal';
                case ACTION.CREATE_RETURN_LABEL:
                    return 'sw-flow-create-return-label-modal';
                default:
                    return this.$super('modalName');
            }
        },
    },

    methods: {
        getActionTitle(actionName) {
            switch (actionName) {
                case ACTION.CHANGE_SHIPPING:
                    return {
                        value: actionName,
                        icon:  'regular-tag',
                        label: this.$tc('change-shipping-action.title'),
                        group: GROUP,
                    };
                case ACTION.CREATE_RETURN_LABEL:
                    return {
                        value: actionName,
                        icon:  'regular-printer',
                        label: this.$tc('create-return-label-action.titleCreateLabel'),
                        group: GROUP,
                    };
                default:
                    return this.$super('getActionTitle', actionName);
            }
        },
    },
});
