@if (! option('hide_pray_for_kyoto_animation', false))
    <span>@lang('general.pray-for-kyoto-animation')</span>
@endif
{!!
    str_replace(
        'Blessing Skin Server',
        '<a href="https://github.com/bs-community/blessing-skin-server">Blessing Skin Server</a>',
        bs_copyright()
    )
!!}
