<div class="item" tid="{{ $texture['tid'] }}">
    <div class="item-body">
        <img src="{{ Http::urlTo('/preview/'.$texture['tid'].'.png') }}">
    </div>

    <div class="item-footer">
        <p class="texture-name">
            <span title="{{ $texture['name'] }}">{{ $texture['name'] }} <small>({{ $texture['type'] }})</small></span>
        </p>


        @if (isset($_SESSION['uid']))

            @if ($user->closet->has($texture['tid']))
            <a title="从衣柜中移除" class="more like liked" tid="{{ $texture['tid'] }}" href="javascript:removeFromCloset({{ $texture['tid'] }});" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>
            @else
            <a title="添加至衣柜" class="more like" tid="{{ $texture['tid'] }}" href="javascript:addToCloset({{ $texture['tid'] }});" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>
            @endif

        @else
        <a title="请先登录" class="more like" href="javascript:;" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>
        @endif

        @if ($texture['public'] == "0")
        <small class="more private-label" tid="{{ $texture['tid'] }}">私密</small>
        @endif

    </div>
</div>
