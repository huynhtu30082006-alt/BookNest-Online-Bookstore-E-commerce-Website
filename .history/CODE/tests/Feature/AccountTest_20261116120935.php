<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private function validRegistration(array $override = []): array
    {
        return array_merge([
            'name' => 'Lan Nguyen',
            'email' => 'lan@example.com',
            'phone' => '0901234567',
            'address' => '12 Nguyen Van Bao, Go Vap, HCMC',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ], $override);
    }

    public function test_user_can_register_and_is_logged_in_as_customer(): void
    {
        $this->post('/register', $this->validRegistration())->assertRedirect('/');

        $this->assertAuthenticated();
        $user = User::where('email', 'lan@example.com')->first();
        $this->assertSame('customer', $user->role);
        $this->assertNotSame('Password123', $user->password); // hashed
    }

    public function test_registration_cannot_set_admin_role(): void
    {
        $this->post('/register', $this->validRegistration(['role' => 'admin']));

        $this->assertSame('customer', User::first()->role);
    }

    public function test_registration_validation(): void
    {
        User::factory()->create(['email' => 'lan@example.com']);

        $this->post('/register', $this->validRegistration())->assertSessionHasErrors('email');
        $this->post('/register', $this->validRegistration(['email' => 'x@y.com', 'password' => 'short', 'password_confirmation' => 'short']))
            ->assertSessionHasErrors('password');
        $this->post('/register', $this->validRegistration(['email' => 'x@y.com', 'phone' => '123']))->assertSessionHasErrors('phone');
        $this->post('/register', $this->validRegistration(['email' => 'x@y.com', 'password_confirmation' => 'Different123']))
            ->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_member_pages(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/orders')->assertRedirect('/login');
    }

    public function test_member_can_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', [
            'name' => 'New Name', 'email' => 'new@example.com', 'phone' => '0911222333', 'address' => '1 Le Loi, District 1, HCMC',
        ])->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('+84911222333', $user->phone);
        $this->assertSame('1 Le Loi, District 1, HCMC', $user->address);
    }

    public function test_profile_email_must_stay_unique(): void
    {
        $other = User::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', [
            'name' => 'X', 'email' => $other->email, 'phone' => '0911222333', 'address' => '',
        ])->assertSessionHasErrors('email');
    }
    
    public function test_phone_can_be_shared_between_accounts(): void
    {
        User::factory()->create(['phone' => '+84901234567']);

        $this->post('/register', $this->validRegistration(['email' => 'new@y.com', 'phone' => '0901 234 567']))
            ->assertSessionHasNoErrors();
        $this->assertSame(2, User::count());
    }
}
