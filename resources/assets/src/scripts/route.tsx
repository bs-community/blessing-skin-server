import React from 'react'

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
    react: () => import('../views/user/Closet'),
    el: '#closet-list',
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
    react: () => import('../views/user/BindPlayers'),
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
    react: () => import('../views/admin/UsersManagement'),
    el: '.content > .container-fluid',
  },
  {
    path: 'admin/players',
    react: () => import('../views/admin/PlayersManagement'),
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
    react: () => import('../views/admin/Translations'),
    el: '#table',
  },
  {
    path: 'admin/plugins/manage',
    react: () => import('../views/admin/PluginsManagement'),
    el: '.content > .container-fluid',
  },
  {
    path: 'admin/plugins/market',
    react: () => import('../views/admin/PluginsMarket'),
    el: '.content > .container-fluid',
  },
  {
    path: 'admin/update',
    module: [() => import('../views/admin/Update')],
  },
  {
    path: 'auth/login',
    react: () => import('../views/auth/Login'),
    el: 'main',
  },
  {
    path: 'auth/register',
    react: () => import('../views/auth/Registration'),
    el: 'main',
  },
  {
    path: 'auth/forgot',
    react: () => import('../views/auth/Forgot'),
    el: 'main',
  },
  {
    path: 'auth/reset/(\\d+)',
    react: () => import('../views/auth/Reset'),
    el: 'main',
  },
  {
    path: 'skinlib',
    react: () => import('../views/skinlib/SkinLibrary'),
    el: '.content-wrapper',
  },
  {
    path: 'skinlib/show/(\\d+)',
    react: () => import('../views/skinlib/Show'),
    el: '#side',
  },
  {
    path: 'skinlib/upload',
    react: () => import('../views/skinlib/Upload'),
    el: '#file-input',
  },
]
