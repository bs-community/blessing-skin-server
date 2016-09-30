<!-- Pagination -->
<ul class="pagination pagination-sm no-margin pull-right">
    <?php
        // build base URL
        $base_url = ($filter != "" && $sort != "") ? "?filter=$filter&sort=$sort&" : "?";

        $base_url .= isset($_GET['uid']) ? "uid={$_GET['uid']}&" : "";
    ?>
    <li><a href="{{ $base_url }}page=1">«</a></li>

    @if ($page != 1)
    <li><a href="{{ $base_url }}page={{ $page-1 }}">{{ $page - 1 }}</a></li>
    @endif

    <li><a href="{{ $base_url }}page={{ $page }}" class="active">{{ $page }}</a></li>

    @if ($total_pages > $page)
    <li><a href="{{ $base_url }}page={{ $page+1 }}">{{ $page+1 }}</a></li>
    @endif

    <li><a href="{{ $base_url }}page={{ $total_pages }}">»</a></li>
</ul>

<select id="page-select" class="pull-right">
    @for ($i = 1; $i <= $total_pages; $i++)
    <option value='{{ $i }}' {{ ($i == $page) ? 'selected="selected"' : '' }}>{{ $i }}</option>
    @endfor
</select>

<p class="pull-right">{{ trans('general.pagination', ['page' => $page , 'total' => $total_pages]) }}</p>
