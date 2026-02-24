<?php

namespace Tests\Feature;

use App\Models\Currency\Currency;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Models\Wallet\Wallet;
use Tests\TestCase;

class OrderTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->artisan('migrate:fresh', ['--seeder' => 'DatabaseTestingSeeder']);

        $user1 = User::orderBy('id', 'asc')->first();
        $user2 = User::orderBy('id', 'desc')->first();

        $currencyBtc = Currency::orderBy('id', 'asc')->first();
        $currencyUsdt = Currency::orderBy('id', 'desc')->first();

        $user1BtcWallet = Wallet::where('user_id', $user1->id)->where('currency_id', $currencyBtc->id)->first();
        $user1UsdtWallet = Wallet::where('user_id', $user1->id)->where('currency_id', $currencyUsdt->id)->first();

        $user2BtcWallet = Wallet::where('user_id', $user2->id)->where('currency_id', $currencyBtc->id)->first();
        $user2UsdtWallet = Wallet::where('user_id', $user2->id)->where('currency_id', $currencyUsdt->id)->first();

        // Increase usdt wallet of user 1
        $user1UsdtWallet->balance_in_wallet = 100000;
        $user1UsdtWallet->save();

        // Increase btc wallet of user 2
        $user1BtcWallet->balance_in_wallet = 100000;
        $user1BtcWallet->save();

        /* START TESTING *********************************************/

        $this->actingAs($user1);
        for($i=0; $i<=500; $i++) {
            $response = $this->post('/api/v1/orders', [
                'market' => "BTC-USDT",
                'type' => 'limit',
                'side' => 'buy',
                'price' => rand(100,1000) . '.00' . rand(1, 100),
                'quantity' => rand(0, 30) . '.00' . rand(1, 100),
                //'quoteQuantity' => rand(1, 100),
            ]);
        }

        $this->actingAs($user1);
        for($i=0; $i<=500; $i++) {

            $response = $this->post('/api/v1/orders', [
                'market' => "BTC-USDT",
                'type' => 'limit',
                'side' => 'sell',
                'price' => rand(13, 20) . '.0000' . rand(1, 100),
                'quantity' => rand(1, 30),
            ]);
        }


        $this->cancelOrders($user1);
        $this->cancelOrders($user2);

        $user1BtcWallet = $user1BtcWallet->fresh();
        $user1UsdtWallet = $user1UsdtWallet->fresh();
        $user2BtcWallet = $user2BtcWallet->fresh();
        $user2UsdtWallet = $user2UsdtWallet->fresh();

        $totalBtc = $user1BtcWallet->balance_in_wallet + $user1BtcWallet->balance_in_order + $user2BtcWallet->balance_in_wallet + $user2BtcWallet->balance_in_order;
        $totalUsdt = $user1UsdtWallet->balance_in_wallet + $user1UsdtWallet->balance_in_order + $user2UsdtWallet->balance_in_wallet + $user2UsdtWallet->balance_in_order;

        echo $totalBtc . '   ' . $totalUsdt;

        $this->assertEquals(100000, $totalBtc);
        $this->assertEquals(100000, $totalUsdt);
    }

    private function cancelOrders($user) {

        $orders = Order::where('user_id', $user->id)->get();

        $this->actingAs($user);

        foreach ($orders as $order) {
            $response = $this->get('/api/v1/orders/cancel?uuid=' . $order->id);
        }
    }
}
