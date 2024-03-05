import styled from '@emotion/styled';
import * as breakpoints from '@/styles/breakpoints';

export const Box = styled.div`
  width: 48%;
  margin: 7px;

  ${breakpoints.lessThan(breakpoints.Breakpoint.lg)} {
    width: 98%;
  }
`;
