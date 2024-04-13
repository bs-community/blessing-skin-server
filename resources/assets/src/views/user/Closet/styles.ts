import styled from '@emotion/styled';

export const Card = styled.div`
  width: 235px;
  transition-property: box-shadow;
  transition-duration: 0.3s;

  &:hover {
    cursor: pointer;
  }

  .card-body {
    background-color: #eff1f0;
  }
`;

export const DropdownButton = styled.span`
  color: var(--gray);
  :hover {
    color: #000;
  }
`;
