import handler from '@/views/admin/Customization'

test('preview color', () => {
  document.body.classList.add('skin-blue')
  const target = document.createElement('input')
  target.value = 'skin-purple'
  handler({ target } as any as Event)

  expect(document.body.classList.contains('skin-purple')).toBeTrue()
})
