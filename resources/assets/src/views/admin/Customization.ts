/* eslint-disable object-curly-newline */
import { fromEvent, merge, of, partition } from 'rxjs'
import { filter, map, pairwise } from 'rxjs/operators'

export function registerNavbarPicker(
  navbar: HTMLElement,
  picker: HTMLDivElement,
  init: string,
): void {
  const color$ = fromEvent(picker, 'click').pipe(
    map((event) => event.target as HTMLElement),
    filter(
      (element): element is HTMLInputElement => element.tagName === 'INPUT',
    ),
    map((element) => element.value),
  )

  merge(of(init), color$)
    .pipe(pairwise())
    .subscribe(([previous, current]) => {
      navbar.classList.replace(`navbar-${previous}`, `navbar-${current}`)
    })

  const [light$, dark$] = partition(color$, (color) =>
    ['light', 'warning', 'white', 'orange', 'lime'].includes(color),
  )
  light$.subscribe(() => {
    // DO NOT use `classList.replace`.
    navbar.classList.remove('navbar-dark')
    navbar.classList.add('navbar-light')
  })
  dark$.subscribe(() => {
    // DO NOT use `classList.replace`.
    navbar.classList.remove('navbar-light')
    navbar.classList.add('navbar-dark')
  })
}

const navbar = document.querySelector<HTMLElement>('.wrapper > nav')
const picker = document.querySelector<HTMLDivElement>('#navbar-color-picker')
/* istanbul ignore next */
if (navbar && picker) {
  registerNavbarPicker(navbar, picker, blessing.extra.navbar || 'white')
}

export function registerSidebarPicker(
  sidebar: HTMLElement,
  { dark, light }: { dark: HTMLDivElement; light: HTMLDivElement },
  init: string,
): void {
  const color$ = merge(
    fromEvent(dark, 'click'),
    fromEvent(light, 'click'),
  ).pipe(
    map((event) => event.target as HTMLElement),
    filter(
      (element): element is HTMLInputElement => element.tagName === 'INPUT',
    ),
    map((element) => element.value),
  )

  merge(of(init), color$)
    .pipe(pairwise())
    .subscribe(([previous, current]) => {
      sidebar.classList.replace(`sidebar-${previous}`, `sidebar-${current}`)
    })
}

const sidebar = document.querySelector<HTMLElement>('.main-sidebar')
const darkPicker = document.querySelector<HTMLDivElement>(
  '#sidebar-dark-picker',
)
const lightPicker = document.querySelector<HTMLDivElement>(
  '#sidebar-light-picker',
)
/* istanbul ignore next */
if (sidebar && darkPicker && lightPicker) {
  registerSidebarPicker(
    sidebar,
    { dark: darkPicker, light: lightPicker },
    blessing.extra.sidebar || 'dark-primary',
  )
}
