<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Important: UserSeeder must run first
        $this->call([
            UserSeeder::class,
            ProjectSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
            SupportSeeder::class,
        ]);
    }
}
