import type {Texture, User} from '@/scripts/types';

export const enum Status {
	Pending = 0,
	Resolved = 1,
	Rejected = 2,
}

export type Report = {
	id: number;
	tid: number;
	texture: Texture | undefined;
	uploader: number;
	texture_uploader: User | undefined;
	reporter: number;
	informer: User | undefined;
	reason: string;
	status: Status;
	report_at: string;
};
