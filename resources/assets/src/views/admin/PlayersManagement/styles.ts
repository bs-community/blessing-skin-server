import * as breakpoints from '@/styles/breakpoints';
import styled from '@emotion/styled';

export const Box = styled.div`
  width: 48%;
  margin: 7px;

  ${breakpoints.lessThan(breakpoints.Breakpoint.lg)} {
    width: 98%;
  }
`;
