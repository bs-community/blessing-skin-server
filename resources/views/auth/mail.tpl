<div style="background:#444;padding:0">
<link rel="stylesheet" type="text/css" href="https://work.prinzeugen.net/font/Minecraft.css">
<div style="min-height:100%;background:#444;padding:80px 0;margin:0;font-size:14px;line-height:1.7;font-family:'Helvetica Neue',Arial,'Microsoft Yahei','Microsoft Jhenghei',sans-serif;color:#444">
<center>
    <div style="margin:0 auto;width:580px;background:#fff;text-align:left">
        <h1 style="margin:0 40px;color:#999;border-bottom:1px dotted #ddd;padding:40px 0 30px;text-align:center;font-size: 35px;font-family:Minecraft,Copperplate,'Copperplate Gothic Light',Palatino,'Palatino Linotype','Palatino LT STD',Georgia,'Microsoft Yahei','Microsoft Jhenghei',serif">
            {{ option_localized('site_name') }}
        </h1>
        <div style="padding:30px 40px 40px">{!! trans('auth.mail.message', ['sitename' => "<a style='color:#009a61;text-decoration:none' href='".option('site_url')."'>".option_localized('site_name')."</a>"]) !!}<br><br>
            <div style="border-left:5px solid #ddd;padding:0 0 0 24px;color:#888">
                {{ trans('auth.mail.ignore')  }}
            </div>
        </div>
        <div style="background:#eee;border-top:1px solid #ddd;text-align:center;min-height:90px;line-height:90px">
            <a href="{{ $reset_url }}" style="padding:8px 18px;background:#009a61;color:#fff;text-decoration:none;border-radius:3px" target="_blank">
                {{ trans('auth.mail.reset') }} ➔
            </a>
        </div>
    </div>
    <div style="padding-top:30px;text-align:center;font-size:12px;color:#999">
        * {{ trans('auth.mail.notice') }} <br>
    </div>
</center>
</div>
<div style="min-height:100%;background:#444;padding:40px 0;margin:0;font-size:14px;line-height:1.7;font-family:'Helvetica Neue',Arial,'Microsoft Yahei','Microsoft Jhenghei',sans-serif;color:#444"></div>
</div>
