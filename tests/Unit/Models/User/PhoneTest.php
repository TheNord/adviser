<?php

namespace Tests\Unit\Models\User;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use phpDocumentor\Reflection\Types\Self_;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PhoneTest extends TestCase
{
    use DatabaseTransactions;

    public function testDefault()
    {
        $user = factory(User::class)->create([
            'phone' => null,
            'phone_verified' => false,
            'phone_verify_token' => null,
        ]);
        
        self::assertFalse($user->isPhoneVerified());
    }

    public function testRequestEmptyPhone()
    {
        $user = factory(User::class)->create([
            'phone' => null,
            'phone_verified' => false,
            'phone_verify_token' => null,
        ]);
        
        $this->expectExceptionMessage('Phone number is empty.');
        $user->requestPhoneVerification(Carbon::now());
    }

    public function testRequest()
    {
        $user = factory(User::class)->create([
            'phone' => '79001232211',
            'phone_verified' => false,
            'phone_verify_token' => null,
        ]);

        $token = $user->requestPhoneVerification(Carbon::now());

        self::assertFalse($user->isPhoneVerified());
        self::assertNotEmpty($token);
    }

    public function testRequstOldPhone()
    {
        $user = factory(User::class)->create([
            'phone' => '79001232211',
            'phone_verified' => true,
            'phone_verify_token' => null,
        ]);

        self::assertTrue($user->isPhoneVerified());

        $user->requestPhoneVerification(Carbon::now());

        self::assertFalse($user->isPhoneVerified());
        self::assertNotEmpty($user->phone_verify_token);
    }

    public function testRequestAlreadySentTimeout()
    {
        $user = factory(User::class)->create([
            'phone' => '79001232211',
            'phone_verified' => true,
            'phone_verify_token' => null,
        ]);

        $user->requestPhoneVerification($now = Carbon::now());
        $user->requestPhoneVerification($now->copy()->addSeconds(500));

        self::assertFalse($user->isPhoneVerified());
    }

    public function testRequestAlreadySent()
    {
        $user = factory(User::class)->create([
            'phone' => '79001232211',
            'phone_verified' => true,
            'phone_verify_token' => null,
        ]);

        $user->requestPhoneVerification($now = Carbon::now());

        $this->expectExceptionMessage('Token is already requested.');
        $user->requestPhoneVerification($now->copy()->addSecond(15));
    }

    public function testVerify()
    {
        $user = factory(User::class)->create([
            'phone' => '79001232211',
            'phone_verified' => false,
            'phone_verify_token' => $token = 'token',
            'phone_verify_token_expire' => $now = Carbon::now(),
        ]);

        self::assertFalse($user->isPhoneVerified());

        $user->verifyPhone($token, $now->copy()->subSecond(15));

        self::assertTrue($user->isPhoneVerified());
    }

    public function testVerifyIncorrectToken()
    {
        $user = factory(User::class)->create([
            'phone' => '79001232211',
            'phone_verified' => false,
            'phone_verify_token' => 'token',
            'phone_verify_token_expire' => $now = Carbon::now(),
        ]);

        $this->expectExceptionMessage('Incorrect verify token.');
        $user->verifyPhone('other_token', $now->copy()->subSecond(15));
    }

    public function testVerifyExpiredToken()
    {
        $user = factory(User::class)->create([
            'phone' => '79001232211',
            'phone_verified' => false,
            'phone_verify_token' => $token = 'token',
            'phone_verify_token_expire' => $now = Carbon::now(),
        ]);

        $this->expectExceptionMessage('Token is expired.');
        $user->verifyPhone($token, $now->copy()->addSecond(500));
    }
}
