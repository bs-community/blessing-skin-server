import Vue from 'vue'

declare module 'vue/types/vue' {
  interface VueConstructor {
    prototype: Vue & {
      $http: {
        get: jest.Mock<any>
        post: jest.Mock<any>
      }
    }
  }
}
