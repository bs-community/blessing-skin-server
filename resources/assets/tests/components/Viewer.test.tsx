import React from 'react'
import { render, fireEvent } from '@testing-library/react'
import { trans } from '@/scripts/i18n'
import Viewer from '@/components/Viewer'

test('custom footer', () => {
  const { queryByText } = render(<Viewer>footer</Viewer>)
  expect(queryByText('footer')).toBeInTheDocument()
})

describe('indicator', () => {
  it('hidden by default', () => {
    const { queryByText } = render(<Viewer skin="skin" />)
    expect(queryByText(trans('general.skin'))).not.toBeInTheDocument()
  })

  it('nothing', () => {
    const { queryByText } = render(<Viewer showIndicator />)
    expect(queryByText(trans('general.skin'))).not.toBeInTheDocument()
    expect(queryByText(trans('general.cape'))).not.toBeInTheDocument()
  })

  it('skin only', () => {
    const { queryByText } = render(<Viewer skin="skin" showIndicator />)
    expect(queryByText(trans('general.skin'))).toBeInTheDocument()
    expect(queryByText(trans('general.cape'))).not.toBeInTheDocument()
  })

  it('cape only', () => {
    const { queryByText } = render(<Viewer cape="cape" showIndicator />)
    expect(queryByText(trans('general.skin'))).not.toBeInTheDocument()
    expect(queryByText(trans('general.cape'))).toBeInTheDocument()
  })

  it('skin and cape', () => {
    const { queryByText } = render(
      <Viewer skin="skin" cape="cape" showIndicator />,
    )
    expect(
      queryByText(`${trans('general.skin')} & ${trans('general.cape')}`),
    ).toBeInTheDocument()
    expect(queryByText(trans('general.skin'))).not.toBeInTheDocument()
    expect(queryByText(trans('general.cape'))).not.toBeInTheDocument()
  })
})

describe('actions', () => {
  it('toggle run', () => {
    const { getByTitle } = render(<Viewer />)
    fireEvent.click(
      getByTitle(`${trans('general.walk')} / ${trans('general.run')}`),
    )
  })

  it('toggle rotation', () => {
    const { getByTitle } = render(<Viewer />)
    fireEvent.click(getByTitle(trans('general.rotation')))
  })

  it('toggle pause', () => {
    const { getByTitle } = render(<Viewer />)
    const icon = getByTitle(trans('general.pause'))
    fireEvent.click(icon)
    expect(icon).toHaveClass('fa-play')
  })

  it('reset', () => {
    const { getByTitle } = render(<Viewer />)
    fireEvent.click(getByTitle(trans('general.reset')))
  })
})
