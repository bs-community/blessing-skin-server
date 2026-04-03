<?php

namespace App\Listeners;

use App\Mail\EmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendEmailVerification
{
    public function handle(User $user)
    {
        if (option('require_verification')) {
            // 生成带有时间戳的签名
            $timestamp = time();
            $uid = $user->uid;
            
            // 使用应用密钥、时间戳和用户ID生成签名
            $signature = hash_hmac('sha256', "{$uid}:{$timestamp}", config('app.key'));
            
            // 存储签名和过期时间到数据库
            $user->email_verification_signature = $signature;
            $user->email_verification_expires_at = Carbon::now()->addHour();
            $user->save();
            
            // 生成验证链接
            $url = URL::route(
                'auth.verify',
                ['uid' => $uid, 'timestamp' => $timestamp, 'signature' => $signature],
                false
            );

            try {
                Mail::to($user->email)->send(new EmailVerification(url($url)));
            } catch (\Exception $e) {
                report($e);
            }
        }
    }
}
