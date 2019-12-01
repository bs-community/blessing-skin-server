import Vue from 'vue'

declare module 'vue/types/vue' {
  interface VueConstructor {
    prototype: Vue & {
      $http: {
        get: jest.Mock<any>
        post: jest.Mock<any>
        put: jest.Mock<any>
        del: jest.Mock<any>
      }
      $t(key: string, parameters?: object): string
    }
  }
}
