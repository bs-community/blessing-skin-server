/* eslint-disable max-len */
import { ElMessage } from 'element-ui/types/message'
import { ElMessageBox } from 'element-ui/types/message-box'

export const Message = {} as jest.Mock<ReturnType<ElMessage>, Parameters<ElMessage>>
export const MessageBox = {} as jest.Mock<ReturnType<ElMessageBox>, Parameters<ElMessageBox>> & {
  alert: jest.Mock<ReturnType<ElMessageBox>, Parameters<ElMessageBox>>
  confirm: jest.Mock<ReturnType<ElMessageBox>, Parameters<ElMessageBox>>
  prompt: jest.Mock<ReturnType<ElMessageBox>, Parameters<ElMessageBox>>
}
