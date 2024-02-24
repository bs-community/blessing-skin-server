/** @jsxImportSource @emotion/react */
import type React from 'react';
import {useState, useEffect, useRef} from 'react';
import {useMeasure} from 'react-use';
import {css} from '@emotion/react';
import styled from '@emotion/styled';
import * as skinview3d from 'skinview3d';
import SkinSteve from '../../../misc/textures/steve.png';
import bg1 from '../../../misc/backgrounds/1.webp';
import bg2 from '../../../misc/backgrounds/2.webp';
import bg3 from '../../../misc/backgrounds/3.webp';
import bg4 from '../../../misc/backgrounds/4.webp';
import bg5 from '../../../misc/backgrounds/5.webp';
import bg6 from '../../../misc/backgrounds/6.webp';
import bg7 from '../../../misc/backgrounds/7.webp';
import * as breakpoints from '@/styles/breakpoints';
import * as cssUtils from '@/styles/utils';
import {t} from '@/scripts/i18n';

const backgrounds = [bg1, bg2, bg3, bg4, bg5, bg6, bg7];
export const PICTURES_COUNT = backgrounds.length;

type Properties = {
	readonly skin?: string;
	readonly cape?: string;
	readonly children?: React.ReactNode;
	readonly isAlex: boolean;
	readonly showIndicator?: boolean;
	readonly initPositionZ?: number;
};

const animationFactories = [
	() => new skinview3d.WalkingAnimation(),
	() => new skinview3d.RunningAnimation(),
	() => new skinview3d.FlyingAnimation(),
	() => new skinview3d.IdleAnimation(),
];

const ActionButton = styled.i`
  display: inline;
  padding: 0.5em 0.5em;
  &:hover {
    color: #555;
    cursor: pointer;
  }
`;

const cssViewer = css`
  flex: 1 1 auto;
  ${breakpoints.greaterThan(breakpoints.Breakpoint.lg)} {
    min-height: 500px;
  }
  min-height: 300px;
  width: 100%;
  height: 100%;

  canvas {
    display: flex;
    justify-content: center;
  }
`;

const Viewer: React.FC<Properties> = properties => {
	const {initPositionZ = 70} = properties;

	const viewReference: React.MutableRefObject<skinview3d.SkinViewer> = useRef(null!);
	const containerReference = useRef<HTMLCanvasElement>(null);

	const [paused, setPaused] = useState(false);
	const [animation, setAnimation] = useState(0);
	const [bgPicture, setBgPicture] = useState(-1);

	const indicator = (() => {
		const {skin, cape} = properties;
		if (skin && cape) {
			return `${t('general.skin')} & ${t('general.cape')}`;
		}

		if (skin) {
			return t('general.skin');
		}

		if (cape) {
			return t('general.cape');
		}

		return '';
	})();

	useEffect(() => {
		const container = containerReference.current!;
		const viewer = new skinview3d.SkinViewer({
			canvas: container,
			width: container.clientWidth,
			height: container.clientHeight,
			skin: properties.skin || SkinSteve,
			cape: properties.cape || undefined,
			model: properties.isAlex ? 'slim' : 'default',
			zoom: initPositionZ / 100,
		});
		viewer.autoRotate = true;

		if (document.body.classList.contains('dark-mode')) {
			viewer.background = '#6c757d';
		}

		viewReference.current = viewer;

		return () => {
			viewer.dispose();
		};
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, []);

	const [containerWrapperReference, containerMeasure] = useMeasure<HTMLDivElement>();
	useEffect(() => {
		viewReference.current.setSize(containerMeasure.width, containerMeasure.height);
	});

	useEffect(() => {
		const viewer = viewReference.current;
		viewer.loadSkin(properties.skin || SkinSteve, {
			model: properties.isAlex ? 'slim' : 'default',
		});
	}, [properties.skin, properties.isAlex]);

	useEffect(() => {
		const viewer = viewReference.current;
		if (properties.cape) {
			viewer.loadCape(properties.cape);
		} else {
			viewer.resetCape();
		}
	}, [properties.cape]);

	useEffect(() => {
		const viewer = viewReference.current;
		const factory = animationFactories[animation];
		if (factory === undefined) {
			viewer.animation = null;
		} else {
			const newAnimation = factory();
			newAnimation.paused = paused; // Perseve `paused` state
			viewer.animation = newAnimation;
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [animation]);

	useEffect(() => {
		const currentAnimation = viewReference.current.animation;
		if (currentAnimation !== null) {
			currentAnimation.paused = paused;
		}
	}, [paused]);

	useEffect(() => {
		const viewer = viewReference.current;
		const backgroundUrl = backgrounds[bgPicture];
		if (backgroundUrl === undefined) {
			viewer.background = null;
		} else {
			viewer.loadBackground(backgroundUrl);
		}
	}, [bgPicture]);

	const togglePause = () => {
		setPaused(paused => {
			if (paused) {
				return false;
			}

			viewReference.current.autoRotate = false;
			return true;
		});
	};

	const toggleAnimation = () => {
		setAnimation(index => (index + 1) % animationFactories.length);
		setPaused(false);
	};

	const toggleRotate = () => {
		const viewer = viewReference.current;
		viewer.autoRotate = !viewer.autoRotate;
	};

	const toggleBackEquippment = () => {
		const player = viewReference.current.playerObject;
		player.backEquipment = player.backEquipment === 'cape' ? 'elytra' : 'cape';
	};

	const setWhite = () => {
		viewReference.current.background = '#fff';
	};

	const setGray = () => {
		viewReference.current.background = '#6c757d';
	};

	const setBlack = () => {
		viewReference.current.background = '#000';
	};

	const setPreviousPicture = () => {
		setBgPicture(index => {
			if (bgPicture <= 0) {
				return PICTURES_COUNT - 1;
			}

			return index - 1;
		});
	};

	const setNextPicture = () => {
		setBgPicture(index => {
			if (bgPicture >= PICTURES_COUNT - 1) {
				return 0;
			}

			return index + 1;
		});
	};

	return (
		<div className='card'>
			<div className='card-header'>
				<div className='d-flex justify-content-between'>
					<h3 className='card-title'>
						<span>{t('general.texturePreview')}</span>
						{properties.showIndicator && (
							<span className='badge bg-olive ml-1'>{indicator}</span>
						)}
					</h3>
					<div>
						<ActionButton
							className={`fas fa-tablet ${properties.cape ? '' : 'd-none'}`}
							data-toggle='tooltip'
							data-placement='bottom'
							title={t('general.switchCapeElytra')}
							onClick={toggleBackEquippment}
						 />
						<ActionButton
							className='fas fa-person-running'
							data-toggle='tooltip'
							data-placement='bottom'
							title={t('general.switchAnimation')}
							onClick={toggleAnimation}
						 />
						<ActionButton
							className={`fas fa-${paused ? 'play' : 'pause'}`}
							data-toggle='tooltip'
							data-placement='bottom'
							title={
								paused
									? t('general.playAnimation')
									: t('general.pauseAnimation')
							}
							onClick={togglePause}
						 />
						<ActionButton
							className='fas fa-rotate-right'
							data-toggle='tooltip'
							data-placement='bottom'
							title={t('general.rotation')}
							onClick={toggleRotate}
						 />
					</div>
				</div>
			</div>
			<div ref={containerWrapperReference} css={cssViewer} className='p-0'>
				<canvas ref={containerReference}/>
			</div>
			<div className='card-footer'>
				<div className='mt-2 mb-3 d-flex'>
					<div
						className='btn-color bg-white rounded-pill mr-2 elevation-2'
						title={t('colors.white')}
						onClick={setWhite}
					/>
					<div
						className='btn-color bg-black rounded-pill mr-2 elevation-2'
						title={t('colors.black')}
						onClick={setBlack}
					/>
					<div
						className='btn-color bg-gray rounded-pill mr-2 elevation-2'
						title={t('colors.gray')}
						onClick={setGray}
					/>
					<div
						className='btn-color bg-green rounded-pill mr-2 elevation-2'
						css={cssUtils.center}
						title={t('colors.prev')}
						onClick={setPreviousPicture}
					>
						<i className='fas fa-arrow-left'/>
					</div>
					<div
						className='btn-color bg-green rounded-pill mr-2 elevation-2'
						css={cssUtils.center}
						title={t('colors.next')}
						onClick={setNextPicture}
					>
						<i className='fas fa-arrow-right'/>
					</div>
				</div>
				{properties.children}
			</div>
		</div>
	);
};

export default Viewer;
