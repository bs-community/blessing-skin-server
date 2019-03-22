export default [
  {
    path: '/',
    module: [() => import('./stylus/home.styl')],
  },
  {
    path: 'user',
    component: () => import('./views/user/Dashboard.vue'),
    el: '#usage-box',
  },
  {
    path: 'user/closet',
    component: () => import('./views/user/Closet.vue'),
    el: '.content',
  },
  {
    path: 'user/player',
    component: () => import('./views/user/Players.vue'),
    el: '.content',
  },
  {
    path: 'user/player/bind',
    component: () => import('./views/user/Bind.vue'),
    el: 'form',
  },
  {
    path: 'user/profile',
    component: () => import('./views/user/Profile.vue'),
    el: '.content',
  },
  {
    path: 'admin',
    module: [() => import('./views/admin/Dashboard')],
  },
  {
    path: 'admin/users',
    component: () => import('./views/admin/Users.vue'),
    el: '.content',
  },
  {
    path: 'admin/players',
    component: () => import('./views/admin/Players.vue'),
    el: '.content',
  },
  {
    path: 'admin/customize',
    component: () => import('./views/admin/Customization.vue'),
    el: '#change-color',
  },
  {
    path: 'admin/plugins/manage',
    component: () => import('./views/admin/Plugins.vue'),
    el: '.content',
  },
  {
    path: 'admin/plugins/market',
    component: () => import('./views/admin/Market.vue'),
    el: '.content',
  },
  {
    path: 'admin/update',
    component: () => import('./views/admin/Update.vue'),
    el: '#update-button',
  },
  {
    path: 'auth/login',
    component: () => import('./views/auth/Login.vue'),
    el: 'form',
  },
  {
    path: 'auth/register',
    component: () => import('./views/auth/Register.vue'),
    el: 'form',
  },
  {
    path: 'auth/forgot',
    component: () => import('./views/auth/Forgot.vue'),
    el: 'form',
  },
  {
    path: 'auth/reset/(\\d+)',
    component: () => import('./views/auth/Reset.vue'),
    el: 'form',
  },
  {
    path: 'skinlib',
    component: () => import('./views/skinlib/List.vue'),
    el: '.content-wrapper',
  },
  {
    path: 'skinlib/show/(\\d+)',
    component: () => import('./views/skinlib/Show.vue'),
    el: '.content > .row:nth-child(1)',
  },
  {
    path: 'skinlib/upload',
    component: () => import('./views/skinlib/Upload.vue'),
    el: '.content',
  },
]
