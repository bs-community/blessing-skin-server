import styled from '@emotion/styled';
import Skeleton from 'react-loading-skeleton';

const ThickSkeleton = styled(Skeleton)`
  line-height: 2;
`;

export default function LoadingRow() {
	return (
		<tr>
			<td colSpan={8}>
				<ThickSkeleton/>
			</td>
		</tr>
	);
}
