import styled from '@emotion/styled';
import Skeleton from 'react-loading-skeleton';

const TableRow = styled.tr`
  height: 64px;
`;
const ThickSkeleton = styled(Skeleton)`
  line-height: 2;
`;

export default function RowLoading() {
	return (
		<TableRow>
			<td colSpan={3}>
				<ThickSkeleton/>
			</td>
		</TableRow>
	);
}
