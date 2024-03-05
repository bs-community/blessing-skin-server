import type {Filter} from './types';
import {t} from '@/scripts/i18n';
import {TextureType} from '@/scripts/types';

export function humanizeType(type: Filter): string {
	switch (type) {
		case TextureType.Steve: {
			return 'Steve';
		}

		case TextureType.Alex: {
			return 'Alex';
		}

		default: {
			return t(`general.${type}`);
		}
	}
}
