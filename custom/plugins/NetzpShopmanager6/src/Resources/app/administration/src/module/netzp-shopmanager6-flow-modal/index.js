import template from './netzp-shopmanager6-flow-modal.html.twig';
const { Component } = Shopware;

Component.register('netzp-shopmanager6-flow-modal', {
    template,

    props: {
        sequence: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            template: '',
        };
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent()
        {
            this.template = this.sequence?.config?.template || '';
        },

        onClose()
        {
            this.$emit('modal-close');
        },

        onAddAction()
        {
            const sequence = {
                ...this.sequence,
                config: {
                    ...this.config,
                    template: this.template
                },
            };

            this.$emit('process-finish', sequence);
        },
    },
});
