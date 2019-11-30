export function truthy(message: string) {
  return (value?: unknown): string | void => {
    if (!value) {
      return message
    }
  }
}
