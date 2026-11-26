<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PriceList;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guests_are_sent_to_login(): void
    {
        foreach (['/home', '/users', '/lists', '/orders', '/myOrders'] as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }

        $this->post('/bill')->assertRedirect('/login');
    }

    public function test_back_office_is_admin_only(): void
    {
        $ironmonger = User::factory()->create();

        foreach (['/users', '/users/create', '/lists', '/list/createList', '/chooseList', '/allTransfers', "/users/{$ironmonger->id}/edit"] as $uri) {
            $this->actingAs($ironmonger)->get($uri)->assertForbidden();
        }

        $this->actingAs($ironmonger)->post('/users', ['name' => 'Intruso'])->assertForbidden();
    }

    public function test_admin_reaches_the_back_office(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/users')->assertOk();
        $this->actingAs($admin)->get('/lists')->assertOk();
    }

    public function test_orders_are_only_visible_to_their_parties(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR]);
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->order($seller, $buyer);

        $this->actingAs($buyer)->get("/detailMyOrder?id={$order->id}")->assertOk();
        $this->actingAs($stranger)->get("/detailMyOrder?id={$order->id}")->assertNotFound();
        $this->actingAs($stranger)->get("/detailOrder?id={$order->id}")->assertNotFound();
        $this->actingAs($stranger)->post('/downloadPDF', ['id' => $order->id])->assertNotFound();
    }

    public function test_only_the_seller_manages_an_order(): void
    {
        Mail::fake();
        $seller = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR]);
        $buyer = User::factory()->create();
        $order = $this->order($seller, $buyer);

        $this->actingAs($buyer)->post('/changeStatus', ['id' => $order->id, 'status' => Order::STATUS_DELIVERED])->assertNotFound();
        $this->actingAs($buyer)->post('/quotation', ['idOrder' => $order->id, 'ids' => '', 'prices' => ''])->assertNotFound();
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);

        $this->actingAs($seller)->post('/changeStatus', ['id' => $order->id, 'status' => Order::STATUS_APPROVED])->assertRedirect();
        $this->assertSame(Order::STATUS_APPROVED, $order->fresh()->status);
    }

    public function test_users_only_open_their_own_password_form(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->get("/users/{$user->id}/passwordEdit")->assertOk();
        $this->actingAs($user)->get("/users/{$other->id}/passwordEdit")->assertNotFound();
    }

    public function test_hardware_stores_can_register_with_complete_data(): void
    {
        Mail::fake();
        $list = PriceList::query()->forceCreate(['name' => 'Ferreteros PEN', 'role' => User::ROLE_IRONMONGER, 'currency' => 'PEN', 'country' => 'Peru']);

        $this->post('/register', ['name' => 'Ferretería Lima', 'email' => 'ferreteria@example.com'])
            ->assertSessionHasErrors(['phone', 'country', 'city', 'document_type', 'document_number', 'establishment_name']);

        $this->post('/register', [
            'name' => 'Ferretería Lima',
            'email' => 'ferreteria@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'phone' => '987654321',
            'country' => 'Peru',
            'city' => 'Lima',
            'document_type' => 'NIT',
            'document_number' => '20123456789',
            'establishment_name' => 'Ferretería Lima',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'ferreteria@example.com',
            'role' => User::ROLE_IRONMONGER,
            'price_list_id' => $list->id,
        ]);
    }

    private function order(User $seller, User $buyer): Order
    {
        return Order::query()->forceCreate([
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
            'seller_role' => $seller->role,
            'buyer_role' => $buyer->role,
            'status' => Order::STATUS_PENDING,
            'currency' => 'COP',
            'total' => 150000,
            'delivery_address' => 'Calle 10 # 20-30',
        ]);
    }
}
