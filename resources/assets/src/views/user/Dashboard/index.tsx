import React, {useState, useEffect, useCallback} from 'react';
import styled from '@emotion/styled';
import {hot} from 'react-hot-loader/root';
import InfoBox from './InfoBox';
import SignButton from './SignButton';
import * as scoreUtils from './scoreUtils';
import useEmitMounted from '@/scripts/hooks/useEmitMounted';
import {t} from '@/scripts/i18n';
import * as fetch from '@/scripts/net';
import {toast} from '@/scripts/notify';
import useTween from '@/scripts/hooks/useTween';
import urls from '@/scripts/urls';
import * as breakpoints from '@/styles/breakpoints';

type ScoreInfo = {
	signAfterZero: boolean;
	signGapTime: number;
	rate: {players: number; storage: number};
	usage: {players: number; storage: number};
	user: {score: number; lastSignAt: string};
};

type SignReturn = {
	score: number;
};

const ScoreTitle = styled.p`
  font-weight: bold;
  margin-top: 5px;
  ${breakpoints.lessThan(breakpoints.Breakpoint.md)} {
    margin-top: 12px;
  }
`;
const Score = styled.p`
  font-family: 'Minecraft';
  font-size: 50px;
  margin-top: 20px;
  cursor: help;
`;
const ScoreNotice = styled.p`
  font-size: smaller;
  margin-top: 20px;
`;

const Dashboard: React.FC = () => {
	const [loading, setLoading] = useState(false);
	const [players, setPlayers] = useState(0);
	const [storage, setStorage] = useState(0);
	const [score, setScore] = useState(0);
	const [tweenedScore, setTweenedScore] = useTween(0);
	const [playersRate, setPlayersRate] = useState(1);
	const [storageRate, setStorageRate] = useState(1);
	const [lastSign, setLastSign] = useState(new Date());
	const [canSignAfterZero, setCanSignAfterZero] = useState(false);
	const [signGap, setSignGap] = useState(24);

	useEmitMounted();

	useEffect(() => {
		const fetchInfo = async () => {
			setLoading(true);
			const data = await fetch.get<ScoreInfo>(urls.user.score());
			setPlayers(data.usage.players);
			setStorage(data.usage.storage);
			setTweenedScore(data.user.score);
			setScore(data.user.score);
			setPlayersRate(data.rate.players);
			setStorageRate(data.rate.storage);
			setLastSign(new Date(data.user.lastSignAt));
			setCanSignAfterZero(data.signAfterZero);
			setSignGap(data.signGapTime);
			setLoading(false);
		};

		fetchInfo();
	}, []);

	const handleSign = useCallback(async () => {
		setLoading(true);
		const {code, message, data} = await fetch.post<
		fetch.ResponseBody<SignReturn>
		>(urls.user.sign());

		if (code === 0) {
			toast.success(message);
			setLastSign(new Date());
			setTweenedScore(data.score);
			setScore(data.score);
		} else if (code === 1) {
			const remainingTime = scoreUtils.remainingTime(
				lastSign,
				signGap,
				canSignAfterZero,
			);
			toast.warning(scoreUtils.remainingTimeText(remainingTime));
		} else {
			toast.error(message);
		}

		setLoading(false);
	}, []);

	return (
		<div className='card card-primary card-outline'>
			<div className='card-header'>
				<h3 className='card-title'>{t('user.used.title')}</h3>
			</div>
			<div className='card-body'>
				<div className='row'>
					<div className='col-md-1'/>
					<div className='col-md-6'>
						<InfoBox
							color='teal'
							icon='gamepad'
							name={t('user.used.players')}
							used={players}
							unused={score / playersRate}
							unit=''
						/>
						{storage > 1024 ? (
							<InfoBox
								color='maroon'
								icon='hdd'
								name={t('user.used.storage')}
								used={Math.trunc(storage / 1024)}
								unused={Math.trunc(score / storageRate / 1024)}
								unit='MB'
							/>
						) : (
							<InfoBox
								color='maroon'
								icon='hdd'
								name={t('user.used.storage')}
								used={storage}
								unused={score / storageRate}
								unit='KB'
							/>
						)}
					</div>
					<div className='col-md-4 text-center'>
						<ScoreTitle>{t('user.cur-score')}</ScoreTitle>
						<Score data-toggle='modal' data-target='#modal-score-instruction'>
							{Math.trunc(tweenedScore)}
						</Score>
						<ScoreNotice>{t('user.score-notice')}</ScoreNotice>
					</div>
				</div>
			</div>
			<div className='card-footer'>
				<SignButton
					isLoading={loading}
					lastSign={lastSign}
					canSignAfterZero={canSignAfterZero}
					signGap={signGap}
					onClick={handleSign}
				/>
			</div>
		</div>
	);
};

export default hot(Dashboard);
