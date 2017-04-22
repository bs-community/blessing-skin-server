@forelse ($items as $item)
<div class="item" tid="{{ $item->tid }}">
    <div class="item-body">
        <img src="{{ url('preview/'.$item->tid) }}.png">
    </div>
    <div class="item-footer">
        <p class="texture-name">
            <span title="{{ $item->name }}">{{ $item->name }} <small>({{ $item->type }})</small></span>
        </p>

        <a href="{{ url('skinlib/show/'.$item->tid) }}" title="{{ trans('user.closet.view') }}" class="more" data-toggle="tooltip" data-placement="bottom"><i class="fa fa-share"></i></a>
        <span title="{{ trans('user.closet.more') }}" class="more" data-toggle="dropdown" aria-haspopup="true" id="more-button"><i class="fa fa-cog"></i></span>

        <ul class="dropup dropdown-menu" aria-labelledby="more-button">
            <li><a href="javascript:renameClosetItem({{ $item->tid }}, '{{ $item->name }}');">{{ trans('user.closet.rename.title') }}</a></li>
            <li><a href="javascript:removeFromCloset({{ $item->tid }});">{{ trans('user.closet.remove.title') }}</a></li>
            <li><a href="javascript:setAsAvatar({{ $item->tid }});">{{ trans('user.closet.set-avatar') }}</a></li>
        </ul>
    </div>
</div>
@empty
<div class="empty-msg">
    @if($q)
    {{ trans('skinlib.general.no-result') }}
    @else
    {!! trans('user.closet.empty-msg', ['url' => url('skinlib')]) !!}
    @endif
</div>

@endforelse
