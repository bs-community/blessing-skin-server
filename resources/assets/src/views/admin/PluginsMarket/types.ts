export type Plugin = {
  name: string
  version: string
  title: string
  description: string
  author: string
  installed: string | false
  can_update?: boolean
  dependencies: {
    all: Record<string, string>
    unsatisfied: Record<string, string>
  }
}
