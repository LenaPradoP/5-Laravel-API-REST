<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\DB;

class PassportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Client::where('personal_access_client', 1)->exists()) {
            $client = new Client();
            $client->name = 'Personal Access Client';
            $client->secret = 'secret';
            $client->redirect = 'http://localhost';
            $client->personal_access_client = true;
            $client->password_client = false;
            $client->revoked = false;
            $client->save();
            
            DB::table('oauth_personal_access_clients')->insert([
                'client_id' => $client->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}