export default [
    {
        path: 'user',
        component: () => import('./user/Dashboard'),
        el: '#usage-box'
    },
    {
        path: 'user/closet',
        component: () => import('./user/Closet'),
        el: '.content'
    },
    {
        path: 'user/player',
        component: () => import('./user/Players'),
        el: '.content'
    },
    {
        path: 'user/profile',
        component: () => import('./user/Profile'),
        el: '.content'
    },
    {
        path: 'admin/users',
        component: () => import('./admin/Users'),
        el: '.content'
    },
    {
        path: 'admin/players',
        component: () => import('./admin/Players'),
        el: '.content'
    },
    {
        path: 'admin/customize',
        component: () => import('./admin/Customization'),
        el: '#change-color'
    },
    {
        path: 'auth/login',
        component: () => import('./auth/Login'),
        el: 'form'
    },
];
