import template from './sw-order-list.html.twig';
import './index.scss';
import { Loading } from 'notiflix/build/notiflix-loading-aio';

const { Criteria } = Shopware.Data;
const { Component, Utils, Mixin, Classes: { ShopwareError }, Application } = Shopware;
const { object: { cloneDeep } } = Shopware.Utils;


Component.override('sw-order-list', {
    template: template,
    inject: [
        'PrintDocumentApiService'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isPrinting: false,
        }
    },
    methods: {
        async printDocuments() {
            if (Object.keys(this.selection).length === 0)
                this.createNotificationWarning({ message: "Please select at least one order." });
            else {
                this.isPrinting = true;
                Loading.standard("Printing...");
                try {
                    const res = await this.PrintDocumentApiService.fetchDocuments(this.selection);
                    this.createNotificationSuccess({ message: "Successfully Print." });

                } catch (err) {
                    console.log('error ', err)
                    this.createNotificationError({ message: "Failed to print documents." })
                }

                this.isPrinting = false;
                Loading.remove();
            }
        },
    },
    mounted() {
        Loading.init({ svgColor: "#189eff" });
    }
});
