import * as validators from '@/scripts/validators'

test('truthy', () => {
  const validator = validators.truthy('invalid')
  expect(validator()).toBe('invalid')
  expect(validator(null)).toBe('invalid')
  expect(validator(undefined)).toBe('invalid')
  expect(validator(0)).toBe('invalid')
  expect(validator('')).toBe('invalid')
  expect(validator([])).toBeUndefined()
  expect(validator({})).toBeUndefined()
})
