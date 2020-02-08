import React from 'react'

const virtual = document.createElement('div')

export default [
  {
    path: '/',
    module: [() => import('../styles/home.styl'), () => import('./home-page')],
  },
  {
    path: 'user',
    react: () => import('../views/user/Dashboard'),
    el: '#usage-box',
    frame: () => (
      <div className="card card-primary card-outline">
        <div className="card-header">&nbsp;</div>
        <div className="card-body"></div>
        <div className="card-footer">&nbsp;</div>
      </div>
    ),
  },
  {
    path: 'user/closet',
    component: () => import('../views/user/Closet.vue'),
    el: virtual,
  },
  {
    path: 'user/player',
    react: () => import('../views/user/Players'),
    el: '#players-list',
    frame: () => (
      <div className="card">
        <div className="card-header">&nbsp;</div>
        <div className="card-body p-0"></div>
      </div>
    ),
  },
  {
    path: 'user/player/bind',
    component: () => import('../views/user/Bind.vue'),
    el: 'form',
  },
  {
    path: 'user/profile',
    module: [() => import('../views/user/profile/index')],
  },
  {
    path: 'user/oauth/manage',
    react: () => import('../views/user/OAuth'),
    el: '.content > .container-fluid',
  },
  {
    path: 'admin',
    module: [() => import('../views/admin/Dashboard')],
  },
  {
    path: 'admin/users',
    component: () => import('../views/admin/Users.vue'),
    el: '.content > .container-fluid',
  },
  {
    path: 'admin/players',
    component: () => import('../views/admin/Players.vue'),
    el: '.content > .container-fluid',
  },
  {
    path: 'admin/reports',
    component: () => import('../views/admin/Reports.vue'),
    el: '.content > .container-fluid',
  },
  {
    path: 'admin/customize',
    module: [() => import('../views/admin/Customization')],
  },
  {
    path: 'admin/i18n',
    component: () => import('../views/admin/Translations.vue'),
    el: '#table',
  },
  {
    path: 'admin/plugins/manage',
    react: () => import('../views/admin/PluginsManagement'),
    el: '.content > .container-fluid',
  },
  {
    path: 'admin/plugins/market',
    component: () => import('../views/admin/Market.vue'),
    el: '.content > .container-fluid',
  },
  {
    path: 'admin/update',
    module: [() => import('../views/admin/Update')],
  },
  {
    path: 'auth/login',
    component: () => import('../views/auth/Login.vue'),
    el: 'form',
  },
  {
    path: 'auth/register',
    component: () => import('../views/auth/Register.vue'),
    el: 'form',
  },
  {
    path: 'auth/forgot',
    component: () => import('../views/auth/Forgot.vue'),
    el: 'form',
  },
  {
    path: 'auth/reset/(\\d+)',
    component: () => import('../views/auth/Reset.vue'),
    el: 'form',
  },
  {
    path: 'skinlib',
    component: () => import('../views/skinlib/List.vue'),
    el: '.content-wrapper',
  },
  {
    path: 'skinlib/show/(\\d+)',
    component: () => import('../views/skinlib/Show.vue'),
    el: virtual,
  },
  {
    path: 'skinlib/upload',
    component: () => import('../views/skinlib/Upload.vue'),
    el: virtual,
  },
]
