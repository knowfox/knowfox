<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Model;
use Knowfox\User;
use Carbon\Carbon;


class EmailLogin extends Model
{
    public $fillable = ['email', 'token'];

    public function user()
    {
        return $this->hasOne(User::class, 'email', 'email');
    }

    public static function createForEmail($email)
    {
        return self::create([
            'email' => $email,
            'token' => str_random(20)
        ]);
    }

    public static function validFromToken($token)
    {
        return self::where('token', $token)
            ->where('created_at', '>', Carbon::parse('-24 hours'))
            ->firstOrFail();
    }
}
