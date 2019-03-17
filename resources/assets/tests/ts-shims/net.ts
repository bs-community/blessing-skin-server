import * as net from '../../src/js/net'

export const init = {} as typeof net.init

export const walkFetch = {} as jest.Mock<
  ReturnType<typeof net.walkFetch>,
  Parameters<typeof net.walkFetch>
>

export const get = {} as jest.Mock<
  ReturnType<typeof net.get>,
  Parameters<typeof net.get>
>

export const post = {} as jest.Mock<
  ReturnType<typeof net.post>,
  Parameters<typeof net.post>
>
