export default [
    {
        path: 'user',
        component: () => import('./user/dashboard'),
        el: '#usage-box'
    },
    {
        path: 'user/closet',
        component: () => import('./user/closet'),
        el: '.content'
    },
    {
        path: 'user/profile',
        component: () => import('./user/profile'),
        el: '.content'
    },
    {
        path: 'admin/users',
        component: () => import('./admin/users'),
        el: '.content'
    },
];
