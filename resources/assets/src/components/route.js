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
    {
        path: 'auth/register',
        component: () => import('./auth/Register'),
        el: 'form'
    },
    {
        path: 'auth/forgot',
        component: () => import('./auth/Forgot'),
        el: 'form'
    },
    {
        path: 'auth/reset/(\\d+)',
        component: () => import('./auth/Reset'),
        el: 'form'
    },
    {
        path: 'skinlib',
        component: () => import('./skinlib/List'),
        el: '.content-wrapper'
    },
    {
        path: 'skinlib/upload',
        component: () => import('./skinlib/Upload'),
        el: '.content'
    },
];
