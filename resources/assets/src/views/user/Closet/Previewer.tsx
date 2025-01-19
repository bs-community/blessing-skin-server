import ViewerSkeleton from '@/components/ViewerSkeleton';
import useMount from '@/scripts/hooks/useMount';
import React from 'react';
import ReactDOM from 'react-dom';

const Viewer = React.lazy(async () => import('@/components/Viewer'));

type Props = {
	skin?: string;
	cape?: string;
	children: React.ReactNode;
	isAlex: boolean;
};

const Previewer: React.FC<Props> = props => {
	const container = useMount('#previewer');

	const skin = props.skin ? `${blessing.base_url}/textures/${props.skin}` : '';
	const cape = props.cape ? `${blessing.base_url}/textures/${props.cape}` : '';

	return (
		container
		&& ReactDOM.createPortal(
			<React.Suspense fallback={<ViewerSkeleton/>}>
				<Viewer showIndicator skin={skin} cape={cape} isAlex={props.isAlex}>
					{props.children}
				</Viewer>
			</React.Suspense>,
			container,
		)
	);
};

export default Previewer;
