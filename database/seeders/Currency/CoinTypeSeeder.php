<?php

namespace Database\Seeders\Currency;

use App\Models\Network\Network;
use Illuminate\Database\Seeder;

class CoinTypeSeeder extends Seeder
{
    const ALLOWED_COIN_TYPES = [
        'internal' => [
            'id' => 0,
            'type' => 'coin',
            'name' => 'Internal'
        ],
        'coinpayments' => [
            'id' => 1,
            'type' => 'coin',
            'name' => 'Coinpayments'
        ],
    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::ALLOWED_COIN_TYPES as $slug=>$data) {

            if(Network::whereSlug($slug)->first()) continue;

            $network = new Network();
            $network->id = $data['id'];
            $network->name = $data['name'];
            $network->type = $data['type'];
            $network->slug = $slug;
            $network->save();
        }
    }
}
