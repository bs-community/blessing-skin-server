export default [
	{
		path: 'user',
		react: async () => import('../views/user/Dashboard'),
		el: '#usage-box',
		frame: () => (
			<div className='card card-primary card-outline'>
				<div className='card-header'>&nbsp;</div>
				<div className='card-body'/>
				<div className='card-footer'>&nbsp;</div>
			</div>
		),
	},
	{
		path: 'user/closet',
		react: async () => import('../views/user/Closet'),
		el: '#closet-list',
	},
	{
		path: 'user/player',
		react: async () => import('../views/user/Players'),
		el: '#players-list',
		frame: () => (
			<div className='card'>
				<div className='card-header'>&nbsp;</div>
				<div className='card-body p-0'/>
			</div>
		),
	},
	{
		path: 'user/profile',
		module: [async () => import('../views/user/profile/index')],
	},
	{
		path: 'user/oauth/manage',
		react: async () => import('../views/user/OAuth'),
		el: '.content > .container-fluid',
	},
	{
		path: 'admin',
		module: [async () => import('../views/admin/Dashboard')],
	},
	{
		path: 'admin/users',
		react: async () => import('../views/admin/UsersManagement'),
		el: '.content > .container-fluid',
	},
	{
		path: 'admin/players',
		react: async () => import('../views/admin/PlayersManagement'),
		el: '.content > .container-fluid',
	},
	{
		path: 'admin/reports',
		react: async () => import('../views/admin/ReportsManagement'),
		el: '.content > .container-fluid',
	},
	{
		path: 'admin/customize',
		module: [async () => import('../views/admin/Customization')],
	},
	{
		path: 'admin/i18n',
		react: async () => import('../views/admin/Translations'),
		el: '#table',
	},
	{
		path: 'admin/plugins/manage',
		react: async () => import('../views/admin/PluginsManagement'),
		el: '.content > .container-fluid',
	},
	{
		path: 'admin/plugins/market',
		react: async () => import('../views/admin/PluginsMarket'),
		el: '.content > .container-fluid',
	},
	{
		path: 'admin/update',
		module: [async () => import('../views/admin/Update')],
	},
	{
		path: 'auth/login',
		react: async () => import('../views/auth/Login'),
		el: 'main',
	},
	{
		path: 'auth/register',
		react: async () => import('../views/auth/Registration'),
		el: 'main',
	},
	{
		path: 'auth/forgot',
		react: async () => import('../views/auth/Forgot'),
		el: 'main',
	},
	{
		path: 'auth/reset/(\\d+)',
		react: async () => import('../views/auth/Reset'),
		el: 'main',
	},
	{
		path: 'skinlib',
		react: async () => import('../views/skinlib/SkinLibrary'),
		el: '.content-wrapper',
	},
	{
		path: 'skinlib/show/(\\d+)',
		react: async () => import('../views/skinlib/Show'),
		el: '#side',
	},
	{
		path: 'skinlib/upload',
		react: async () => import('../views/skinlib/Upload'),
		el: '#file-input',
	},
];
