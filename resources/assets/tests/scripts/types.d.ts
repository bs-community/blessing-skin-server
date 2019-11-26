class Request {
  url: string

  headers: Map<string, string>
}

interface Window {
  trans(key: string, parameters: object): string

  blessing: {
    base_url: string
    site_name: string
    version: string
    i18n: object
    extra: object
  }

  fetch: jest.Mock

  $: jest.Mock
}
