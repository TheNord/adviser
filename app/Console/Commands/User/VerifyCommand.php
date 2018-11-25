<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyCommand extends Command
{

    protected $signature = 'user:verify {email}';

    protected $description = 'Verification user';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $email = $this->argument('email');

        if (!$user = User::where('email', $email)->first()) {
            $this->error('Undefined user with email' . $email);
            return false;
        }

        try {
            $user->verify();
        } catch (\DomainException $e) {
            $this->error($e->getMessage());
            return false;
        }


        $this->info('User with email ' . $this->argument('email') . ' successful verified!');
        return true;
    }
}
