import type {FC, ReactNode} from 'react';
import ViewerSkeleton from '@/components/ViewerSkeleton';
import useMount from '@/scripts/hooks/useMount';
import {
	lazy,
	Suspense,
} from 'react';
import {createPortal} from 'react-dom';

const Viewer = lazy(async () => import('@/components/Viewer'));

type Props = {
	skin?: string;
	cape?: string;
	children: ReactNode;
	isAlex: boolean;
};

const Previewer: FC<Props> = props => {
	const container = useMount('#previewer');

	const skin = props.skin === undefined ? '' : `${blessing.base_url}/textures/${props.skin}`;
	const cape = props.cape === undefined ? '' : `${blessing.base_url}/textures/${props.cape}`;

	return (
		container
		&& createPortal(
			<Suspense fallback={<ViewerSkeleton/>}>
				<Viewer showIndicator skin={skin} cape={cape} isAlex={props.isAlex}>
					{props.children}
				</Viewer>
			</Suspense>,
			container,
		)
	);
};

export default Previewer;
