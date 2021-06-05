import React from 'react'
import { render, fireEvent } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import FileInput from '@/components/FileInput'

test('click to select file', () => {
  const { getAllByText } = render(
    <FileInput
      file={null}
      onChange={() => {
        /* */
      }}
    />,
  )

  fireEvent.click(getAllByText(t('skinlib.upload.select-file'))[1]!)
})

test('display file name', () => {
  const file = new File([], 'f.txt')
  const { queryByText } = render(
    <FileInput
      file={file}
      onChange={() => {
        /* */
      }}
    />,
  )
  expect(queryByText('f.txt')).toBeInTheDocument()
})

test('input file', () => {
  const mock = jest.fn()

  const { getByLabelText } = render(<FileInput file={null} onChange={mock} />)
  fireEvent.change(getByLabelText(t('skinlib.upload.select-file')))

  expect(mock).toBeCalled()
})
