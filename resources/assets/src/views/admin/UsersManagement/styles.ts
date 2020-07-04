import styled from '@emotion/styled'
import * as breakpoints from '@/styles/breakpoints'

export const Box = styled.div`
  width: 48%;
  margin: 7px;

  ${breakpoints.lessThan(breakpoints.Breakpoint.lg)} {
    width: 98%;
  }
`

export const Icon = styled.div<{ py?: boolean }>`
  width: 70px;
  display: flex;
  justify-content: center;
  padding-top: ${(props) => (props.py ? '22px' : '0')};
`

export const InfoTable = styled.div`
  > div:not(:last-child) {
    ${breakpoints.lessThan(breakpoints.Breakpoint.sm)} {
      border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    ${breakpoints.greaterThan(breakpoints.Breakpoint.sm)} {
      border-right: 1px solid rgba(0, 0, 0, 0.125);
    }
  }
`
