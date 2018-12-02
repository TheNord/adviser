<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;


/**
 * Class User
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property bool $phone_verified
 * @property string $password
 * @property string $verify_token
 * @property string $phone_verify_token
 * @property Carbon $phone_verify_token_expire
 * @property string $role
 * @property string $status
 * @property boolean $phone_auth
 */
class User extends Authenticatable
{
    use Notifiable;

    public const STATUS_WAIT = "wait";
    public const STATUS_ACTIVE = "active";

    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name', 'last_name', 'email', 'phone', 'password', 'status', 'verify_token', 'role'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $casts = [
        'phone_verified' => 'boolean',
        'phone_verify_token_expire' => 'datetime'
    ];

    public static function register(string $name, string $email, string $password): self
    {
        return static::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'verify_token' => Str::uuid(),
            'status' => self::STATUS_WAIT,
            'role' => 'user',
        ]);
    }

    public static function new($name, $email, $password): self
    {
        return static::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'status' => self::STATUS_ACTIVE,
            'role' => 'user',
        ]);
    }

    public function isWait(): bool
    {
        return $this->status === self::STATUS_WAIT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function verify(): void
    {
        if(!$this->isWait()){
            throw new \DomainException('User is already verified.');
        }

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'verify_token' => null,
        ]);
    }

    public function changeRole($role): void
    {
        if (!\in_array($role, [self::ROLE_USER, self::ROLE_ADMIN], true)) {
            throw new \InvalidArgumentException('Undefined role "' . $role . '"');
        }

        if ($this->role === $role) {
            throw new \DomainException('Role is already assigned.');
        }

        $this->update(['role' => $role]);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /* Phone verify and check methods */

    public function unverifyPhone(): void
    {
        $this->phone_verified = false;
        $this->phone_verify_token = null;
        $this->phone_verify_token_expire = null;

        $this->saveOrFail();
    }

    public function requestPhoneVerification(Carbon $now): string
    {
        if (empty($this->phone)) {
            throw new \DomainException('Phone number is empty.');
        }

        // Если токен и значение даты не пустые и время истечения токена больше чем текущая дата (функция gt($now), $now от Carbon)
        if (!empty($this->phone_verify_token) && $this->phone_verify_token_expire && $this->phone_verify_token_expire->gt($now)) {
            throw new \DomainException('Token is already requested.');
        }

        $this->phone_verified = false;
        $this->phone_verify_token = (string)random_int(10000, 99999);
        $this->phone_verify_token_expire = $now->copy()->addSeconds(300);
        $this->saveOrFail();

        return $this->phone_verify_token;
    }

    public function verifyPhone($token, Carbon $now): void
    {
        if ($token !== $this->phone_verify_token) {
            throw new \DomainException('Incorrect verify token.');
        }
        // Время истечения токена меньше чем текущая дата (lt($now))
        if ($this->phone_verify_token_expire->lt($now)) {
            throw new \DomainException('Token is expired.');
        }

        $this->phone_verified = true;
        $this->phone_verify_token = null;
        $this->phone_verify_token_expire = null;

        $this->saveOrFail();
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified;
    }

    public function isPhoneAuthEnabled(): bool
    {
        return $this->phone_auth;
    }

    public function disablePhoneAuth(): void
    {
        $this->phone_auth = false;

        $this->saveOrFail();
    }

    public function enablePhoneAuth(): void
    {
        $this->phone_auth = true;

        $this->saveOrFail();
    }

}
