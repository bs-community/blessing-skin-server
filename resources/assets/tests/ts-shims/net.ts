/* eslint-disable @typescript-eslint/indent */
import type * as net from '../../src/scripts/net'

export type { ResponseBody } from '../../src/scripts/net'

export const init = {} as typeof net.init

export const walkFetch = {} as jest.Mock<
  ReturnType<typeof net.walkFetch>,
  Parameters<typeof net.walkFetch>
>

type FetchFn = <T = any>(url: string, data?: object) => Promise<T>

export const get = {} as jest.Mock<any, Parameters<typeof net.get>> & FetchFn

export const post = {} as jest.Mock<any, Parameters<typeof net.post>> & FetchFn

export const put = {} as jest.Mock<any, Parameters<typeof net.put>> & FetchFn

export const del = {} as jest.Mock<any, Parameters<typeof net.del>> & FetchFn
