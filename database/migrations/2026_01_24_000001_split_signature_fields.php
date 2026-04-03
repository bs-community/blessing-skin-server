<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SplitSignatureFields extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // 移除之前添加的通用字段
            $table->dropColumn(['verification_signature', 'signature_expires_at']);
            
            // 添加密码重置专用字段
            $table->string('password_reset_signature')->nullable()->after('verified');
            $table->timestamp('password_reset_expires_at')->nullable()->after('password_reset_signature');
            
            // 添加邮箱验证专用字段
            $table->string('email_verification_signature')->nullable()->after('password_reset_expires_at');
            $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_signature');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // 删除新添加的独立字段
            $table->dropColumn([
                'password_reset_signature',
                'password_reset_expires_at',
                'email_verification_signature',
                'email_verification_expires_at'
            ]);
            
            // 恢复之前的通用字段
            $table->string('verification_signature')->nullable();
            $table->timestamp('signature_expires_at')->nullable();
        });
    }
}