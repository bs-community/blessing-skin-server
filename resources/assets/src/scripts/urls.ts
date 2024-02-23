export default {
	admin: {
		players: {
			delete: (player: number) => `/admin/players/${player}`,
			list: () => '/admin/players/list' as const,
			name: (player: number) => `/admin/players/${player}/name`,
			owner: (player: number) => `/admin/players/${player}/owner`,
			texture: (player: number) => `/admin/players/${player}/textures`,
		},
		users: {
			delete: (user: number) => `/admin/users/${user}`,
			email: (user: number) => `/admin/users/${user}/email`,
			list: () => '/admin/users/list' as const,
			nickname: (user: number) => `/admin/users/${user}/nickname`,
			password: (user: number) => `/admin/users/${user}/password`,
			permission: (user: number) => `/admin/users/${user}/permission`,
			score: (user: number) => `/admin/users/${user}/score`,
			verification: (user: number) => `/admin/users/${user}/verification`,
		},
	},
	auth: {
		bind: () => '/auth/bind' as const,
		forgot: () => '/auth/forgot' as const,
		login: () => '/auth/login' as const,
		logout: () => '/auth/logout' as const,
		register: () => '/auth/register' as const,
		reset: (uid: number) => `/auth/reset/${uid}`,
		verify: (uid: number) => `/auth/verify/${uid}`,
	},
	skinlib: {
		home: () => '/skinlib' as const,
		info: (texture: number) => `/skinlib/info/${texture}`,
		list: () => '/skinlib/list' as const,
		show: (tid: number) => `/skinlib/show/${tid}`,
	},
	texture: {
		delete: (texture: number) => `/texture/${texture}`,
		info: (texture: number) => `/texture/${texture}`,
		name: (texture: number) => `/texture/${texture}/name`,
		privacy: (texture: number) => `/texture/${texture}/privacy`,
		type: (texture: number) => `/texture/${texture}/type`,
		upload: () => '/texture' as const,
	},
	user: {
		closet: {
			add: () => '/user/closet' as const,
			ids: () => '/user/closet/ids' as const,
			list: () => '/user/closet/list' as const,
			page: () => '/user/closet' as const,
			remove: (tid: number) => `/user/closet/${tid}`,
			rename: (tid: number) => `/user/closet/${tid}`,
		},
		home: () => '/user' as const,
		notification: (id: number) => `/user/notifications/${id}`,
		player: {
			add: () => '/user/player' as const,
			clear: (player: number) => `/user/player/${player}/textures`,
			delete: (player: number) => `/user/player/${player}`,
			list: () => '/user/player/list' as const,
			page: () => '/user/player' as const,
			rename: (player: number) => `/user/player/${player}/name`,
			set: (player: number) => `/user/player/${player}/textures`,
		},
		profile: {avatar: () => '/user/profile/avatar' as const},
		score: () => '/user/score-info' as const,
		sign: () => '/user/sign' as const,
	},
};
