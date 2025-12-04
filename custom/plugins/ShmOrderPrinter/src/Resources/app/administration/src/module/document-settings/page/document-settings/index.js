const { Component, Mixin } = Shopware;

import template from './document-settings.html.twig';
import deDE from '../../snippet/de-DE.json';
import enGB from '../../snippet/en-GB.json';
Component.register('document-settings', {
   template,
   mixins: [
      Mixin.getByName('notification')
   ],
   inject: ['DocumentSettingsApiService'],
   snippets: {
      'de-DE': deDE,
      'en-GB': enGB,
   },
   data() {
      return {
         documentTypes: [],
         settings: [],
         salesChannels: [],
         isLoading: false,
         settingsId: "",
         savingSetting: false,
         failedToFetched: false,
      }
   },
   methods: {
      changeSettings(e, id, value) {
         // Fix: Initialisiere leeres Array falls Verkaufskanal noch nicht existiert
         let arr = [...(this.settings[id] || [])];
         let setting = {};

         if (e.target.checked) {
            // Nur hinzufügen wenn nicht bereits vorhanden
            if (!arr.includes(value)) {
               arr.push(value);
            }
         } else {
            arr.splice(arr.indexOf(value), 1);
         }

         setting[id] = arr;
         this.settings = { ...this.settings, ...setting };
      },
      async saveSettings() {
         this.savingSetting = true;
         try {
            const status = await this.DocumentSettingsApiService.saveSettings(this.settings, this.settingsId);
            if (status === 204)
               this.createNotificationSuccess({ message: "Successfully Saved." });
            else
               this.createNotificationError({ message: "Fail save settings." })
         } catch (err) {
            this.createNotificationError({ message: "Fail save settings." })
         }
         this.savingSetting = false;
      },
   },
   async mounted() {
      this.isLoading = true;
      try {
         const [settings, documenttypes, channels] = await this.DocumentSettingsApiService.getSttings();
         this.settings = settings?.data[0]?.attributes?.setting?.data || {};
         this.settingsId = settings?.data[0]?.id;
         this.documentTypes = [
            ...documenttypes?.data,
            ...[
               {
                  id: "dhl_shipping_label",
                  attributes: {
                     name: "Shipping Label",
                     technicalName: "dhl_shipping_label",
                  }
               },
               {
                  id: "dhl_return_label",
                  attributes: {
                     name: "Return Label",
                     technicalName: "dhl_return_label",
                  }
               },
               {
                  id: "single_dhl_return_label",
                  attributes: {
                     name: "Single Return Label",
                     technicalName: "single_dhl_return_label",
                  }
               },
               {
                  id: "seven_sender_lables_return",
                  attributes: {
                     name: "Seven Senders Label Return",
                     technicalName: "seven_sender_lables_return",
                  }
               },
               {
                  id: "seven_sender_lables_outbond",
                  attributes: {
                     name: "Seven Senders Label Outbond",
                     technicalName: "seven_sender_lables_outbond",
                  }
               }
            ]
         ];
         this.salesChannels = channels?.data;
         this.failedToFetched = false;
      } catch (err) {
         this.createNotificationError({ message: "Fail to fetch settings." });
         this.failedToFetched = true;
      }
      this.isLoading = false;;
   },
});
