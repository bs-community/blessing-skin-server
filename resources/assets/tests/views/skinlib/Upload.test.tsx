import React from 'react'
import { render, fireEvent, wait } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { isAlex } from '@/scripts/textureUtils'
import Upload from '@/views/skinlib/Upload'

jest.mock('@/scripts/net')
jest.mock('@/scripts/textureUtils')

beforeEach(() => {
  const container = document.createElement('div')
  container.id = 'previewer'
  document.body.appendChild(container)

  window.blessing.extra = {
    rule: 'rule',
    privacyNotice: 'privacy notice',
    scorePrivate: 10,
    scorePublic: 1,
    closetItemCost: 10,
    award: 0,
    contentPolicy: 'the policy',
  }

  URL.createObjectURL = jest.fn().mockReturnValue('')
})

afterEach(() => {
  document.querySelector('#previewer')!.remove()
})

test('display texture name rule', () => {
  const { queryByPlaceholderText } = render(<Upload />)
  expect(queryByPlaceholderText('rule')).toBeInTheDocument()
})

test('content policy', () => {
  const { queryByText } = render(<Upload />)
  expect(queryByText('the policy')).toBeInTheDocument()
})

test('privacy notice', () => {
  const { getByLabelText, queryByText } = render(<Upload />)
  fireEvent.click(getByLabelText(t('skinlib.upload.set-as-private')))
  expect(queryByText('privacy notice')).toBeInTheDocument()
})

test('award notice', () => {
  Object.assign(window.blessing.extra, { award: 5 })

  const { getByLabelText, queryByText } = render(<Upload />)
  expect(
    queryByText(t('skinlib.upload.award', { score: 5 })),
  ).toBeInTheDocument()

  fireEvent.click(getByLabelText(t('skinlib.upload.set-as-private')))
  expect(
    queryByText(t('skinlib.upload.award', { score: 5 })),
  ).not.toBeInTheDocument()
})

describe('input file', () => {
  it('cancelled', () => {
    const { getByTitle } = render(<Upload />)

    fireEvent.change(getByTitle(t('skinlib.upload.select-file')))
  })

  it('add file', () => {
    const { getByTitle, queryByDisplayValue, queryByText } = render(<Upload />)

    const file = new File([], 't.png')
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })

    expect(queryByDisplayValue('t')).toBeInTheDocument()
    expect(queryByText('t.png')).toBeInTheDocument()
  })

  it('do not overwrite existing name', () => {
    const {
      getByTitle,
      getByLabelText,
      queryByDisplayValue,
      queryByText,
    } = render(<Upload />)

    fireEvent.input(getByLabelText(t('skinlib.upload.texture-name')), {
      target: { value: 'my texture' },
    })

    const file = new File([], 't.png')
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })

    expect(queryByDisplayValue('my texture')).toBeInTheDocument()
    expect(queryByText('t.png')).toBeInTheDocument()
  })

  it('select skin type automatically', async () => {
    isAlex.mockResolvedValue(true)

    const { getByTitle, getByLabelText } = render(<Upload />)

    const file = new File([], 't.png')
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })

    await wait()
    expect(getByLabelText('Alex')).toBeChecked()
  })

  it('do not overwrite "cape" type', async () => {
    const { getByTitle, getByLabelText } = render(<Upload />)

    fireEvent.click(getByLabelText(t('general.cape')))
    await wait()

    const file = new File([], 't.png')
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })

    await wait()
    expect(getByLabelText(t('general.cape'))).toBeChecked()
  })
})

describe('score cost', () => {
  it('public texture', () => {
    const { getByTitle, queryByText } = render(<Upload />)

    const file = new File([], 't.png')
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })

    expect(
      queryByText(t('skinlib.upload.cost', { score: 11 })),
    ).toBeInTheDocument()
  })

  it('private texture', () => {
    const { getByTitle, getByLabelText, queryByText } = render(<Upload />)

    const file = new File([], 't.png')
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })
    fireEvent.click(getByLabelText(t('skinlib.upload.set-as-private')))

    expect(
      queryByText(t('skinlib.upload.cost', { score: 20 })),
    ).toBeInTheDocument()
  })
})

describe('upload texture', () => {
  it('no file', () => {
    const { getByText, getByRole, queryByText } = render(<Upload />)
    fireEvent.click(getByText(t('skinlib.upload.button')))
    expect(queryByText(t('skinlib.emptyUploadFile'))).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })

  it('no name', () => {
    const {
      getByText,
      getByLabelText,
      getByTitle,
      getByRole,
      queryByText,
    } = render(<Upload />)

    const file = new File([], 't.png')
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })
    fireEvent.input(getByLabelText(t('skinlib.upload.texture-name')), {
      target: { value: '' },
    })
    fireEvent.click(getByText(t('skinlib.upload.button')))

    expect(queryByText(t('skinlib.emptyTextureName'))).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })

  it('no name', () => {
    const { getByText, getByTitle, getByRole, queryByText } = render(<Upload />)

    const file = new File([], 't.png', { type: 'image/jpeg' })
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })
    fireEvent.click(getByText(t('skinlib.upload.button')))

    expect(queryByText(t('skinlib.fileExtError'))).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })

  it('uploading', () => {
    fetch.post.mockResolvedValue({ code: 1, message: '' })

    const { getByText, getByTitle, queryByText } = render(<Upload />)

    const file = new File([], 't.png', { type: 'image/png' })
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })
    fireEvent.click(getByText(t('skinlib.upload.button')))

    expect(queryByText(t('skinlib.uploading'))).toBeInTheDocument()
    expect(fetch.post).toBeCalledWith('/skinlib/upload', expect.any(FormData))

    const formData: FormData = fetch.post.mock.calls[0][1]
    expect(formData.get('name')).toBe('t')
    expect(formData.get('type')).toBe('steve')
    expect(formData.get('file')).toStrictEqual(file)
    expect(formData.get('public')).toBe('true')
  })

  it('uploaded successfully', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok', tid: 1 })

    const { getByText, getByTitle } = render(<Upload />)

    const file = new File([], 't.png', { type: 'image/png' })
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })
    fireEvent.click(getByText(t('skinlib.upload.button')))

    await wait()
  })

  it('duplicated texture detected', async () => {
    fetch.post.mockResolvedValue({ code: 2, message: 'dup', tid: 1 })

    const { getByText, getByTitle, queryByText } = render(<Upload />)

    const file = new File([], 't.png', { type: 'image/png' })
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })
    fireEvent.click(getByText(t('skinlib.upload.button')))

    await wait()
    expect(queryByText('dup')).toBeInTheDocument()

    fireEvent.click(getByText(t('user.viewInSkinlib')))
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByTitle, getByRole, queryByText } = render(<Upload />)

    const file = new File([], 't.png', { type: 'image/png' })
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })
    fireEvent.click(getByText(t('skinlib.upload.button')))

    await wait()
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })
})
