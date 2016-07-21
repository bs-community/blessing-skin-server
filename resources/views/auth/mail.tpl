<div style="background:#444;padding:0">
<link rel="stylesheet" type="text/css" href="https://work.prinzeugen.net/font/Minecraft.css">
<div style="min-height:100%;background:#444;padding:80px 0;margin:0;font-size:14px;line-height:1.7;font-family:'Helvetica Neue',Arial,'Microsoft Yahei','Microsoft Jhenghei',sans-serif;color:#444">
<center>
    <div style="margin:0 auto;width:580px;background:#fff;text-align:left">
        <h1 style="margin:0 40px;color:#999;border-bottom:1px dotted #ddd;padding:40px 0 30px;text-align:center;font-size: 35px;font-family:Minecraft,'Helvetica Neue',Arial,'Microsoft Yahei','Microsoft Jhenghei',sans-serif">
            {{ Option::get('site_name') }}
        </h1>
        <div style="padding:30px 40px 40px">您收到这封邮件，是因为在 <a style="color:#009a61;text-decoration:none" href="{{ Option::get('site_url') }}">我们网站</a> 的用户重置密码功能使用了您的地址。<br><br>
            <div style="border-left:5px solid #ddd;padding:0 0 0 24px;color:#888">
                如果您并没有访问过我们的网站，或没有进行上述操作，请忽略这封邮件。 您不需要退订或进行其他进一步的操作。
            </div>
        </div>
        <div style="background:#eee;border-top:1px solid #ddd;text-align:center;min-height:90px;line-height:90px">
            <a href="{{ $reset_url }}" style="padding:8px 18px;background:#009a61;color:#fff;text-decoration:none;border-radius:3px" target="_blank">
                重置密码 ➔
            </a>
        </div>
    </div>
    <div style="padding-top:30px;text-align:center;font-size:12px;color:#999">
        * 本邮件由系统自动发送，就算你回复了我们也不会回复你哦 <br>
    </div>
</center>
</div>
<div style="min-height:100%;background:#444;padding:40px 0;margin:0;font-size:14px;line-height:1.7;font-family:'Helvetica Neue',Arial,'Microsoft Yahei','Microsoft Jhenghei',sans-serif;color:#444"></div>
</div>
