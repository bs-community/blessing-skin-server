import Vue from 'vue'
import { Message, MessageBox } from 'element-ui'
import { ElMessageBoxOptions } from 'element-ui/types/message-box'

declare module 'vue/types/vue' {
  interface VueConstructor {
    prototype: Vue & {
      $http: {
        get: jest.Mock<any>
        post: jest.Mock<any>
        put: jest.Mock<any>
        del: jest.Mock<any>
      },
      $message: {
        info: jest.Mock<ReturnType<typeof Message>, Parameters<typeof Message>>
        success: jest.Mock<ReturnType<typeof Message>, Parameters<typeof Message>>
        warning: jest.Mock<ReturnType<typeof Message>, Parameters<typeof Message>>
        error: jest.Mock<ReturnType<typeof Message>, Parameters<typeof Message>>
      }
      $msgbox: jest.Mock<ReturnType<typeof MessageBox>, [ElMessageBoxOptions]>
      $alert: jest.Mock<ReturnType<typeof MessageBox>, [string, ElMessageBoxOptions]>
      $confirm: jest.Mock<ReturnType<typeof MessageBox>, [string, ElMessageBoxOptions]>
      $prompt: jest.Mock<ReturnType<typeof MessageBox>, [string, ElMessageBoxOptions]>
    }
  }
}
