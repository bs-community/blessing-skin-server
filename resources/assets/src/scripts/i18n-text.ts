// @ts-ignore
import { locale } from 'element-ui'
import { emit } from './event'

const langs = {
  en: () => import('element-ui/lib/locale/lang/en'),
  zh_CN: () => import('element-ui/lib/locale/lang/zh-CN'),
}

async function load(language: import('../shims').I18n) {
  locale.use((await langs[language]()).default)
  emit('i18nLoaded', blessing.i18n)
}

export default function () {
  return blessing.locale in langs
    ? load(blessing.locale)
    : load(blessing.fallback_locale)
}
