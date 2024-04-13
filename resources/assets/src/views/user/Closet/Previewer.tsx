import React from 'react';
import ReactDOM from 'react-dom';
import useMount from '@/scripts/hooks/useMount';
import ViewerSkeleton from '@/components/ViewerSkeleton';

const Viewer = React.lazy(async () => import('@/components/Viewer'));

type Properties = {
	skin?: string;
	cape?: string;
	children: React.ReactNode;
	isAlex: boolean;
};

const Previewer: React.FC<Properties> = properties => {
	const container = useMount('#previewer');

	const skin = properties.skin ? `${blessing.base_url}/textures/${properties.skin}` : '';
	const cape = properties.cape ? `${blessing.base_url}/textures/${properties.cape}` : '';

	return (
		container
    && ReactDOM.createPortal(
    	<React.Suspense fallback={<ViewerSkeleton/>}>
    		<Viewer showIndicator skin={skin} cape={cape} isAlex={properties.isAlex}>
    			{properties.children}
 </Viewer>
 </React.Suspense>,
    	container,
    )
	);
};

export default Previewer;
