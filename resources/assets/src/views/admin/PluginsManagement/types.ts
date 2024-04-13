export type Plugin = {
	name: string;
	title: string;
	description: string;
	version: string;
	enabled: boolean;
	config: boolean;
	readme: boolean;
	icon: {fa: string; faType: 'fas' | 'fab'; bg: string};
};
