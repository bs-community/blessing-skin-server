export const enum Breakpoint {
  xs = 0,
  sm = 576,
  md = 768,
  lg = 992,
  xl = 1200,
}

export function lessThan(breakpoint: Breakpoint): string {
  return `@media (max-width: ${breakpoint}px)`
}

export function between(down: Breakpoint, up: Breakpoint): string {
  return `@media (min-width: ${down}px) and (max-width: ${up}px)`
}

export function greaterThan(breakpoint: Breakpoint): string {
  return `@media (min-width: ${breakpoint}px)`
}
