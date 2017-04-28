<ol class="breadcrumb">
    <li><i class="fa fa-tags"></i> {{ trans('skinlib.filter.now-showing') }}</li>
    <li>
        @if ($filter == "skin")
            {{ trans('skinlib.filter.skin') }}
            <small>{{ trans('skinlib.filter.any-model') }}</small>
        @elseif ($filter == "steve")
            {{ trans('skinlib.filter.skin') }}
            <small>{{ trans('skinlib.filter.steve-model') }}</small>
        @elseif ($filter == "alex")
            {{ trans('skinlib.filter.skin') }}
            <small>{{ trans('skinlib.filter.alex-model') }}</small>
        @elseif ($filter == "cape")
            {{ trans('skinlib.filter.cape') }}
        @endif
    </li>
    @unless ($uploader == 0)
    <li>{{ trans('skinlib.filter.uploader', ['name' => App::make('users')->get($_GET['uid'])->getNickName()]) }}</li>
    @endunless
    <li class="active">
        @if ($sort == "time")
            {{ trans('skinlib.sort.newest-uploaded') }}
        @elseif ($sort == "likes")
            {{ trans('skinlib.sort.most-likes') }}
        @endif
    </li>
</ol>
