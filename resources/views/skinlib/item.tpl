<div class="item" tid="{{ $texture['tid'] }}">
    <div class="item-body">
        <img src="{{ url('preview/'.$texture['tid'].'.png') }}">
    </div>

    <div class="item-footer">
        <p class="texture-name">
            <span title="{{ $texture['name'] }}">{{ $texture['name'] }} <small>({{ $texture['type'] }})</small></span>
        </p>


        @if (Session::has('uid'))

            @if ($user->closet->has($texture['tid']))
            <a title="{{ trans('skinlib.item.remove-from-closet') }}" class="more like liked" tid="{{ $texture['tid'] }}" href="javascript:removeFromCloset({{ $texture['tid'] }});" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>
            @else
            <a title="{{ trans('skinlib.item.add-to-closet') }}" class="more like" tid="{{ $texture['tid'] }}" href="javascript:addToCloset({{ $texture['tid'] }});" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>
            @endif

        @else
        <a title="{{ trans('skinlib.item.not-logged-in') }}" class="more like" href="javascript:;" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>
        @endif

        @if ($texture['public'] == "0")
        <small class="more private-label" tid="{{ $texture['tid'] }}">{{ trans('skinlib.item.private') }}</small>
        @endif

    </div>
</div>
