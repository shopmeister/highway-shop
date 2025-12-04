import { ACTION, GROUP } from '../../constant/netzp-shopmanager6.constant';

const { Component } = Shopware;

Component.override('sw-flow-sequence-action', {
    computed: {
        modalName()
        {
            if (this.selectedAction === ACTION.CREATE_MOBILE_PUSH) {
                return 'netzp-shopmanager6-flow-modal';
            }

            return this.$super('modalName');
        },

        actionDescription() {
            const actionDescriptionList = this.$super('actionDescription');

            return {
                ...actionDescriptionList,
                [ACTION.CREATE_MOBILE_PUSH] : (config) => this.getMobilePushDescription(config),
            };
        },
    },

    methods: {
        getMobilePushDescription(config) {
            var description = '';

            if (config.template) {
                description = config.template;
            }

            return this.$tc('netzp-shopmanager6.flow.modal.description') + ': <i>' + description + '</i>';
        },

        getActionTitle(actionName)
        {
            if (actionName === ACTION.CREATE_MOBILE_PUSH)
            {
                return {
                    value: actionName,
                    icon: 'regular-mobile',
                    label: this.$tc('netzp-shopmanager6.flow.modal.actionTitle'),
                    group: GROUP
                }
            }

            return this.$super('getActionTitle', actionName);
        },
    },
});
