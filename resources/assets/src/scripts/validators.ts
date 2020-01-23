export function truthy(message: string) {
  return (value?: unknown): string | undefined => {
    if (!value) {
      return message
    }
  }
}
