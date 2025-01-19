import {useEffect, useState} from 'react';

export default function useIsLargeScreen() {
	const [isLarge, setIsLarge] = useState(false);

	useEffect(() => {
		if (window.innerWidth >= 992) {
			setIsLarge(true);
		}
	}, []);

	return isLarge;
}
