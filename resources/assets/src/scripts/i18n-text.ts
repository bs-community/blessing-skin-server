import { locale as elementLocale } from 'element-ui'
import { emit } from './event'

declare module 'element-ui/types/element-ui' {
  export const locale: {
    use(i18n: any): void
  }
}

const langs = {
  en: {
    bs: () => import('../../../lang/en/front-end.yml'),
    element: () => import('element-ui/lib/locale/lang/en'),
  },
  zh_CN: {
    bs: () => import('../../../lang/zh_CN/front-end.yml'),
    element: () => import('element-ui/lib/locale/lang/zh-CN'),
  },
}

async function load(language: import('../shims').I18n) {
  const { bs: loadBS, element: loadElement } = langs[language]
  await Promise.all([
    (async () => {
      const text = await loadBS()
      blessing.i18n = Object.assign(blessing.i18n || Object.create(null), text)
      emit('i18nLoaded', blessing.i18n)
    })(),
    (async () => {
      elementLocale.use((await loadElement()).default)
    })(),
  ])
}

export default function () {
  return blessing.locale in langs
    ? load(blessing.locale)
    : load(blessing.fallback_locale)
}
