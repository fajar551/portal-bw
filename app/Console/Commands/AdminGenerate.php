<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genrate DUMY Admin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = 'admin';
        $email = 'admin@admin.com';
        $plainpassword = 'password';
        $password = Hash::make($plainpassword);

        $admin = Admin::firstOrCreate(
            [
                'id' => 1,
                'username' => $username,
                'email' => $email,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'password' => $password,
                'firstname' => 'Admin',
                'lastname' => 'CBMS',
                'template' => 'admin',
                'notes' => 'DUMY',
                'language' => 'id',
                'disabled' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]
        );

        return $this->info("Admin DUMY generated. Email: {$email} Password: $plainpassword");
    }
}
