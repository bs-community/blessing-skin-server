export default [
  {
    path: 'user',
    component: () => import('./user/Dashboard.vue'),
    el: '#usage-box',
  },
  {
    path: 'user/closet',
    component: () => import('./user/Closet.vue'),
    el: '.content',
  },
  {
    path: 'user/player',
    component: () => import('./user/Players.vue'),
    el: '.content',
  },
  {
    path: 'user/profile',
    component: () => import('./user/Profile.vue'),
    el: '.content',
  },
  {
    path: 'admin/users',
    component: () => import('./admin/Users.vue'),
    el: '.content',
  },
  {
    path: 'admin/players',
    component: () => import('./admin/Players.vue'),
    el: '.content',
  },
  {
    path: 'admin/customize',
    component: () => import('./admin/Customization.vue'),
    el: '#change-color',
  },
  {
    path: 'admin/plugins/manage',
    component: () => import('./admin/Plugins.vue'),
    el: '.content',
  },
  {
    path: 'admin/plugins/market',
    component: () => import('./admin/Market.vue'),
    el: '.content',
  },
  {
    path: 'admin/update',
    component: () => import('./admin/Update.vue'),
    el: '#update-button',
  },
  {
    path: 'auth/login',
    component: () => import('./auth/Login.vue'),
    el: 'form',
  },
  {
    path: 'auth/register',
    component: () => import('./auth/Register.vue'),
    el: 'form',
  },
  {
    path: 'auth/forgot',
    component: () => import('./auth/Forgot.vue'),
    el: 'form',
  },
  {
    path: 'auth/reset/(\\d+)',
    component: () => import('./auth/Reset.vue'),
    el: 'form',
  },
  {
    path: 'skinlib',
    component: () => import('./skinlib/List.vue'),
    el: '.content-wrapper',
  },
  {
    path: 'skinlib/show/(\\d+)',
    component: () => import('./skinlib/Show.vue'),
    el: '.content > .row:nth-child(1)',
  },
  {
    path: 'skinlib/upload',
    component: () => import('./skinlib/Upload.vue'),
    el: '.content',
  },
]
