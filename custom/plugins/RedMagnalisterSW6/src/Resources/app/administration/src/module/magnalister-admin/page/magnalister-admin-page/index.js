/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

import template from './magnalister-admin-page.html.twig';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('magnalister-admin-page', {
    template,
    inject: [
        'repositoryFactory',
        'MagnalisterOrderService',
        'systemConfigApiService',
    ],
    data() {
        return {
            config: {},
            selectedField:'',
            repository: null,
            entity: null,
            loaded: false,
            keyId: '',
            iframe: {
                src: null,
                style: null,
                wrapperStyle: null,
            },
            selectboxsaleschannel: false,
            hmac: '',
            sessionIsDeleted: false
        }
    },
    mounted() {
        let editor = this.$refs.editor;
        this.iframe.style = {
            position: 'absolute',
            width: '100%',
            height: '100%',
            top: -editor.offsetTop + "px",
            left: -editor.offsetLeft + "px",
        }
        this.iframe.wrapperStyle = {
            overflow: 'hidden',
            height: editor.clientHeight + "px",
            width: editor.clientWidth + "px",
        }
        const initContainer = Shopware.Application.getContainer('init');
        const me = this;
        let client = initContainer.httpClient;
        me.MagnalisterOrderService.addSession().then((response) => {
            me.keyId = response;
            if (me.keyId.length > 0) {
                let intervalid1 = setInterval(function () {
                    me.MagnalisterOrderService.resetSession(me.keyId);
                }, 60000);
                Shopware.Application.getContainer('service').loginService.addOnLogoutListener(() => {
                    console.log('in addOnLogoutListener save:userKey', me.keyId);
                    if (me.sessionIsDeleted === false) {
                        me.MagnalisterOrderService.deleteSession(me.keyId).then((response) => {
                            console.log(response);
                            me.sessionIsDeleted = true;
                        });
                    }
                });
                const currentClass = this;
                try {
                    const salesChannelRepository = this.repositoryFactory.create('sales_channel');
                    const criteria = new Criteria();
                    criteria.addAssociation('domains');
                    criteria.addAssociation('type');
                    criteria.addSorting(Criteria.sort('sales_channel.maintenance', 'ASC'));
                    criteria.addFilter(
                        Criteria.equals('sales_channel.active', true)
                    );
                    criteria.addFilter(
                        Criteria.equals('sales_channel.type.name', 'Storefront')
                    );
                    console.log('version', '15.06.2023 10:17:00');//that is important to update and fill manually with current time, to be sure that js is generated correctly
                    salesChannelRepository
                        .search(criteria, Shopware.Context.api)
                        .then((result) => {
                            result.forEach((salesChannel) => {
                                console.log('salesChannel.id', salesChannel.id);
                                salesChannel.domains.forEach((domain) => {
                                    //let currentUrl = window.location.hostname;
                                    const currentUrlProtocol = window.location.protocol;//get the https or http from page url
                                    //get the currentUrl from page url
                                    var currentUrl = window.location.href;//get the full url from page for example http://swdev6671.test/de/admin#/magnalister/admin/page
                                    currentUrl = currentUrl.substring(0, currentUrl.indexOf('/admin#'));//remove extran page to get the domain path from page url for example http://swdev6671.test/de
                                    const arr3=currentUrl.split("/");//put page url as array in arr3
                                    currentUrl= currentUrl.replace(arr3[0]+"//", "");//remove http// or https// from curent url to get the current path form example swdev6671.test/de

                                    // currentUrl= currentUrl + 'test';

                                    const arr2 = domain.url.split("/");
                                    const domainUrlProtocol = arr2[0];
                                    const domainUrlpathname = arr2[3];
                                    console.log('domain.url', domain.url);
                                    var hostdomain = document.createElement("a");
                                    hostdomain.href = domain.url;
                                    let domainHostname = hostdomain.hostname;
                                    console.log('hostdomain.host', hostdomain.hostname);
                                    console.log('currentUrl', currentUrl);
                                    console.log('domainUrlProtocol', domainUrlProtocol);
                                    console.log('currentUrlProtocol', currentUrlProtocol);
                                    console.log('domain.url.split("/")', domain.url.split("/"));
                                    console.log('hostdomain', hostdomain);
                                    console.log('hostdomain.host', hostdomain.host);
                                    console.log('hostdomain.pathname',hostdomain.pathname);
                                    console.log('domainUrlpathname',domainUrlpathname);
                                    console.log('domainHostname',domainHostname );
                                    if(domainUrlpathname){
                                        // adding path url if the domain configured as something like  http://swdev6671.test/de and the result is  swdev6671.test/de
                                        domainHostname = hostdomain.hostname + hostdomain.pathname;
                                        console.log('domainHostname2',domainHostname);
                                    }
                                    if (domainHostname === currentUrl && domainUrlProtocol === currentUrlProtocol) {
                                        console.log('load iframe', currentClass.loaded);
                                        if (!currentClass.loaded) {
                                            currentClass.iframe.src = domain.url + '/magnalister/' + me.keyId + '/' + Shopware.State.get('session').currentUser.id;
                                            currentClass.loaded = true;
                                            currentClass.selectboxsaleschannel = false;
                                        }
                                    }


                                });
                            });
                            if (!currentClass.loaded) {
                                currentClass.iframe.src = 'magnalister/' + me.keyId + '/' + Shopware.State.get('session').currentUser.id;
                                var http = new XMLHttpRequest();
                                http.open('HEAD',  currentClass.iframe.src, false);
                                http.send();
                                if(http.status != 200) {
                                    currentClass.selectboxsaleschannel = true;
                                    this.iframe.style.top = '74px';
                                    this.assignStoreVlaueToSelectBox();
                                    this.systemConfigApiService.getValues('magnalister').then((values) => {
                                        //this.defaultArReady = values['magnalister'];
                                        const salesChanneldomainRepository = this.repositoryFactory.create('sales_channel_domain');
                                        const criteria = new Criteria();
                                        criteria.addFilter(
                                            Criteria.equals('id', values['magnalister.sales'])
                                        );
                                        salesChanneldomainRepository
                                            .search(criteria, Shopware.Context.api)
                                            .then((result) => {
                                                currentClass.iframe.src =  result.first().url+'/magnalister/' + me.keyId + '/' + Shopware.State.get('session').currentUser.id;
                                                console.log('  currentClass.iframe.src',  currentClass.iframe.src);
                                                currentClass.loaded = true;
                                            });
                                    });
                                }else{
                                    currentClass.loaded = true;
                                }
                                currentClass.loaded = true;
                            }
                        });
                } catch (e) {
                    console.log(e);
                    if (!this.loaded) {
                        currentClass.iframe.src = 'magnalister/' + me.keyId + '/' + Shopware.State.get('session').currentUser.id;
                        currentClass.loaded = true;
                    }
                }

            }
        });


    },
    methods: {
        assignStoreVlaueToSelectBox(){
            //Assign stored domain from system_config table to the select box
            this.systemConfigApiService.getValues('magnalister').then((values) => {
                this.selectedField =  values['magnalister.sales'];
                console.log('this.selectedField',this.selectedField);
            });
        },
        selectSalesChannel(id, item) {
            //Store selected domain to the system_config table
            this.$set(this.config, 'magnalister.sales',id);
            this.systemConfigApiService.saveValues(this.config)
            console.log('magnalister.sales configuration value',id);
            setTimeout(() => {
                window.location.reload();
            }, "5000");
        },


    },


});
