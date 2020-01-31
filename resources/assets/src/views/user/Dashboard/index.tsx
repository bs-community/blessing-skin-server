import React, { useState, useEffect, useCallback } from 'react'
import { hot } from 'react-hot-loader/root'
import { trans } from '../../../scripts/i18n'
import * as fetch from '../../../scripts/net'
import { toast } from '../../../scripts/notify'
import useTween from '../../../scripts/hooks/useTween'
import InfoBox from './InfoBox'
import SignButton from './SignButton'
import scoreStyle from './score.scss'

type ScoreInfo = {
  signAfterZero: boolean
  signGapTime: number
  stats: { players: Stat; storage: Stat }
  user: { score: number; lastSignAt: string }
}

type Stat = {
  used: number
  total: number
}

type SignReturn = {
  score: number
  storage: Stat
}

const Dashboard: React.FC = () => {
  const [loading, setLoading] = useState(false)
  const [players, setPlayers] = useState<Stat>({ used: 0, total: 1 })
  const [storage, setStorage] = useState<Stat>({ used: 0, total: 1 })
  const [score, setScore] = useTween(0)
  const [lastSign, setLastSign] = useState(new Date())
  const [canSignAfterZero, setCanSignAfterZero] = useState(false)
  const [signGap, setSignGap] = useState(24)

  useEffect(() => {
    const fetchInfo = async () => {
      setLoading(true)
      const { data } = await fetch.get<fetch.ResponseBody<ScoreInfo>>(
        '/user/score-info',
      )
      setPlayers(data.stats.players)
      setStorage(data.stats.storage)
      setScore(data.user.score)
      setLastSign(new Date(data.user.lastSignAt))
      setCanSignAfterZero(data.signAfterZero)
      setSignGap(data.signGapTime)
      setLoading(false)
    }
    fetchInfo()
  }, [])

  const handleSign = useCallback(async () => {
    setLoading(true)
    const { code, message, data } = await fetch.post<
      fetch.ResponseBody<SignReturn>
    >('/user/sign')

    if (code === 0) {
      toast.success(message)
      setLastSign(new Date())
      setScore(data.score)
      setStorage(data.storage)
    } else {
      toast.warning(message)
    }
    setLoading(false)
  }, [])

  return (
    <div className="card card-primary card-outline">
      <div className="card-header">
        <h3 className="card-title">{trans('user.used.title')}</h3>
      </div>
      <div className="card-body">
        <div className="row">
          <div className="col-md-1"></div>
          <div className="col-md-6">
            <InfoBox
              color="teal"
              icon="gamepad"
              name={trans('user.used.players')}
              used={players.used}
              total={players.total}
              unit=""
            />
            {storage.used > 1024 ? (
              <InfoBox
                color="maroon"
                icon="hdd"
                name={trans('user.used.storage')}
                used={~~(storage.used / 1024)}
                total={~~(storage.total / 1024)}
                unit="MB"
              />
            ) : (
              <InfoBox
                color="maroon"
                icon="hdd"
                name={trans('user.used.storage')}
                used={storage.used}
                total={storage.total}
                unit="KB"
              />
            )}
          </div>
          <div className="col-md-4 text-center">
            <p className={scoreStyle.title}>{trans('user.cur-score')}</p>
            <p
              className={scoreStyle.number}
              data-toggle="modal"
              data-target="#modal-score-instruction"
            >
              {~~score}
            </p>
            <p className={scoreStyle.notice}>{trans('user.score-notice')}</p>
          </div>
        </div>
      </div>
      <div className="card-footer">
        <SignButton
          isLoading={loading}
          lastSign={lastSign}
          canSignAfterZero={canSignAfterZero}
          signGap={signGap}
          onClick={handleSign}
        />
      </div>
    </div>
  )
}

export default hot(Dashboard)
