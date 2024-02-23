import {useEffect} from 'react';
import {emit} from '../event';

export default function useEmitMounted() {
	useEffect(() => {
		emit('mounted');
	}, []);
}
