<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        User::flushEventListeners();

        $quantityUsers = 1500;

        factory(User::class, $quantityUsers)->create();
    }
}
