import React, { useState } from 'react'
import { render, fireEvent } from '@testing-library/react'
import EmailSuggestion from '@/components/EmailSuggestion'

const Wrapper: React.FC = () => {
  const [email, setEmail] = useState('')

  return <EmailSuggestion value={email} onChange={setEmail} />
}

test('basic typing', () => {
  const { getByDisplayValue, queryByText } = render(<Wrapper />)
  const input = getByDisplayValue('')

  fireEvent.input(input, { target: { value: 'abc' } })
  fireEvent.focus(input)
  expect(queryByText('abc@qq.com')).toBeInTheDocument()
  expect(queryByText('abc@163.com')).toBeInTheDocument()
  expect(queryByText('abc@gmail.com')).toBeInTheDocument()
  expect(queryByText('abc@hotmail.com')).toBeInTheDocument()

  fireEvent.input(input, { target: { value: '' } })
  expect(queryByText('abc@qq.com')).not.toBeInTheDocument()
})

test('apply suggestion', () => {
  const { getByDisplayValue, getByText } = render(<Wrapper />)
  const input = getByDisplayValue('')

  fireEvent.input(input, { target: { value: 'abc' } })
  fireEvent.focus(input)
  fireEvent.click(getByText('abc@hotmail.com'))
  expect(input).toHaveValue('abc@hotmail.com')
})

test('do not suggest when `at` is existed', () => {
  const { getByDisplayValue, queryByText } = render(<Wrapper />)
  const input = getByDisplayValue('')

  fireEvent.input(input, { target: { value: 'abc@outlook.com' } })
  fireEvent.focus(input)
  expect(queryByText('abc@outlook.com@qq.com')).not.toBeInTheDocument()
  expect(queryByText('abc@outlook.com@163.com')).not.toBeInTheDocument()
  expect(queryByText('abc@outlook.com@gmail.com')).not.toBeInTheDocument()
  expect(queryByText('abc@outlook.com@hotmail.com')).not.toBeInTheDocument()
})

test('display suggestions when typing with configured domain names', () => {
  const { getByDisplayValue, queryByText } = render(<Wrapper />)
  const input = getByDisplayValue('')

  fireEvent.input(input, { target: { value: 'abc@hotmail.com' } })
  fireEvent.focus(input)
  expect(queryByText('abc@qq.com')).toBeInTheDocument()
  expect(queryByText('abc@163.com')).toBeInTheDocument()
  expect(queryByText('abc@gmail.com')).toBeInTheDocument()
  expect(queryByText('abc@hotmail.com')).toBeInTheDocument()
})
