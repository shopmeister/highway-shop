// eslint-disable-next-line no-undef
Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'marketing',
    key: 'magnalister_admin_page',
    roles: {
        viewer: {
            privileges: [
                'magnalister.admin.page:read',
            ],
            dependencies: [],
        },
    },
});