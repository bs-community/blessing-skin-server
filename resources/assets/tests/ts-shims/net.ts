/* eslint-disable @typescript-eslint/indent */
import * as net from '../../src/scripts/net'

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

export const put = {} as jest.Mock<
  ReturnType<typeof net.post>,
  Parameters<typeof net.post>
>

export const del = {} as jest.Mock<
  ReturnType<typeof net.post>,
  Parameters<typeof net.post>
>
