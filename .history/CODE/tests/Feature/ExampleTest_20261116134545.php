<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Customer-flow test suite for BookNest (owner: Huynh Thanh Tu).
 *
 * Scope
 *   1. Git repository and project coordination (repository hygiene checks)
 *   2. Registration, login / logout, profile, order history
 *   3. Cart, guest / member pricing, delivery fee
 *   4. Coupon validation (members only, one use per customer)
 *   5. Guest / member checkout, simulated payment
 *   6. Order confirmation, customer-side order status, tracking and cancelling
 *   7. End-to-end journeys
 *
 * Location : CODE/tests/Feature/CustomerFlowTest.php
 * Run all  : php artisan test --filter=CustomerFlowTest
 * Run one  : php artisan test --filter=test_member_can_cancel_pending_order
 *
 * The suite runs on an in-memory SQLite database (see phpunit.xml) and never touches PostgreSQL.
 * All money expectations are built with Money::vnd() so they follow the same formatting as the UI.
 */
class CustomerFlowTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // Helpers
    // =====================================================================

    /** A book that costs 100.000 ₫ for guests and 90.000 ₫ for members. */
    private function book(array $attrs = []): Product
    {
        return Product::factory()->create($attrs + ['guest_price' => 100000, 'member_price' => 90000, 'stock' => 5]);
    }

    private function member(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    private function registration(array $override = []): array
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

    private function delivery(array $override = []): array
    {
        return array_merge([
            'name' => 'Minh Tran',
            'email' => 'minh@example.com',
            'phone' => '0987654321',
            'address' => '25 Le Loi, District 1, Ho Chi Minh City',
            'payment_method' => 'cod',
        ], $override);
    }

    private function coupon(array $attrs = []): Coupon
    {
        return Coupon::factory()->create($attrs + ['code' => 'SAVE25', 'discount_value' => 25000, 'min_order_value' => 200000]);
    }

    /** Puts $qty copies of $product in the current session's cart. */
    private function inCart(Product $product, int $qty = 1): void
    {
        $this->post("/cart/add/{$product->id}", ['quantity' => $qty]);
    }

    /** Guest checkout of a fresh book; returns the created order. */
    private function placeGuestOrder(array $delivery = [], int $qty = 2, array $productAttrs = []): Order
    {
        $product = $this->book($productAttrs);
        $this->inCart($product, $qty);
        $this->post('/checkout', $this->delivery($delivery));

        return Order::with('items.product')->latest('id')->firstOrFail();
    }

    private function repoRoot(): string
    {
        return dirname(base_path());
    }

    /** Skips repository tests when the app is run without the full project folder (e.g. CODE/ only). */
    private function requireRepoRoot(): string
    {
        $root = $this->repoRoot();
        if (! is_file($root.'/README.md') || ! is_dir($root.'/DOCUMENTATION')) {
            $this->markTestSkipped('Project root (README.md + DOCUMENTATION/) was not found next to CODE/.');
        }

        return $root;
    }

    /** @return array<int,string> non-empty, trimmed lines of CODE/.gitignore */
    private function gitignoreLines(): array
    {
        return array_values(array_filter(array_map('trim', file(base_path('.gitignore')))));
    }

    // =====================================================================
    // 1. Git repository and project coordination
    // =====================================================================

    public function test_repo_gitignore_protects_secrets_and_generated_folders(): void
    {
        $lines = $this->gitignoreLines();

        foreach (['.env', '/vendor', '/node_modules', '.phpunit.result.cache'] as $entry) {
            $this->assertContains($entry, $lines, "CODE/.gitignore must ignore {$entry}");
        }
    }

    public function test_repo_env_example_is_safe_to_commit_and_targets_postgresql(): void
    {
        $this->assertFileExists(base_path('.env.example'));
        $example = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('DB_CONNECTION=pgsql', $example);
        $this->assertStringContainsString('DB_DATABASE=booknest', $example);
        $this->assertMatchesRegularExpression('/^DB_PASSWORD=\s*$/m', $example, 'No real DB password may be committed.');
        $this->assertMatchesRegularExpression('/^APP_KEY=\s*$/m', $example, 'No real APP_KEY may be committed.');
    }

    public function test_repo_automated_tests_use_isolated_in_memory_database(): void
    {
        $phpunit = file_get_contents(base_path('phpunit.xml'));

        $this->assertStringContainsString('name="DB_CONNECTION" value="sqlite"', $phpunit);
        $this->assertStringContainsString('name="DB_DATABASE" value=":memory:"', $phpunit);
    }

    public function test_repo_has_migrations_for_every_table_of_the_customer_flow(): void
    {
        foreach (['users', 'add_profile_fields_to_users', 'categories', 'products', 'coupons', 'orders', 'order_items'] as $name) {
            $this->assertNotEmpty(
                glob(base_path("database/migrations/*_{$name}_table.php")),
                "Missing migration for {$name}"
            );
        }
    }

    public function test_repo_root_has_the_agreed_folder_structure(): void
    {
        $root = $this->requireRepoRoot();

        foreach (['CODE', 'DATABASE', 'DATA', 'DOCUMENTATION', 'VIDEO'] as $folder) {
            $this->assertDirectoryExists("{$root}/{$folder}", "Missing top-level folder {$folder}/");
        }
        $this->assertFileExists("{$root}/README.md");
        $this->assertFileExists("{$root}/VIDEO/DEMO_LINK.txt");
    }

    public function test_repo_documentation_files_exist(): void
    {
        $root = $this->requireRepoRoot();

        foreach (['SETUP.md', 'STUDENT_INFO.md', 'CUSTOMER_FLOW.md'] as $file) {
            $this->assertFileExists("{$root}/DOCUMENTATION/{$file}");
        }
    }

    public function test_repo_student_info_lists_repository_team_and_every_member(): void
    {
        $root = $this->requireRepoRoot();
        $info = file_get_contents("{$root}/DOCUMENTATION/STUDENT_INFO.md");

        $this->assertStringContainsString('WEB_2627302_01', $info);
        $this->assertStringContainsString('https://github.com/', $info, 'The GitHub repository link must be listed.');
        foreach (['Huynh Thanh Tu', 'Truong Ho Anh Thu', 'Tran Van Linh'] as $member) {
            $this->assertStringContainsString($member, $info);
        }
    }

    public function test_repo_readme_explains_how_to_run_and_test_the_project(): void
    {
        $root = $this->requireRepoRoot();
        $readme = file_get_contents("{$root}/README.md");

        foreach (['composer install', 'php artisan key:generate', 'php artisan migrate:fresh --seed', 'php artisan test', 'lan@example.com', 'WELCOME25'] as $needle) {
            $this->assertStringContainsString($needle, $readme, "README.md should mention: {$needle}");
        }
    }

    public function test_repo_git_does_not_track_secrets_dependencies_or_editor_history(): void
    {
        $root = $this->requireRepoRoot();
        if (! is_dir("{$root}/.git") || ! function_exists('exec')) {
            $this->markTestSkipped('Not a Git working copy (or exec() is disabled).');
        }

        $output = [];
        $code = 0;
        exec('git -C '.escapeshellarg($root).' ls-files -- CODE/.env CODE/vendor CODE/node_modules .history CODE/.history 2>&1', $output, $code);

        if ($code !== 0) {
            $this->markTestSkipped('The git command is not available.');
        }
        $this->assertSame([], $output, 'These files must never be committed: '.implode(', ', array_slice($output, 0, 5)));
    }

    // =====================================================================
    // 2a. Registration
    // =====================================================================

    public function test_registration_page_is_available_to_guests(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_user_can_register_and_is_logged_in_as_customer(): void
    {
        $this->post('/register', $this->registration())->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $user = User::where('email', 'lan@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertSame('+84901234567', $user->phone);
        $this->assertNotSame('Password123', $user->password, 'Password must be stored hashed.');
    }

    public function test_registration_cannot_create_an_admin(): void
    {
        $this->post('/register', $this->registration(['role' => 'admin']));

        $this->assertSame('customer', User::firstOrFail()->role);
    }

    public function test_registration_requires_valid_data(): void
    {
        User::factory()->create(['email' => 'lan@example.com']);

        $cases = [
            'duplicate email' => [['email' => 'lan@example.com'], 'email'],
            'invalid email' => [['email' => 'not-an-email'], 'email'],
            'missing name' => [['email' => 'a@b.com', 'name' => ''], 'name'],
            'missing phone' => [['email' => 'a@b.com', 'phone' => ''], 'phone'],
            'password too short' => [['email' => 'a@b.com', 'password' => 'Ab1', 'password_confirmation' => 'Ab1'], 'password'],
            'password without numbers' => [['email' => 'a@b.com', 'password' => 'abcdefgh', 'password_confirmation' => 'abcdefgh'], 'password'],
            'password without letters' => [['email' => 'a@b.com', 'password' => '12345678', 'password_confirmation' => '12345678'], 'password'],
            'password not confirmed' => [['email' => 'a@b.com', 'password_confirmation' => 'Different123'], 'password'],
        ];

        foreach ($cases as [$override, $field]) {
            $this->post('/register', $this->registration($override))->assertSessionHasErrors($field);
            $this->assertGuest();
        }
        $this->assertSame(1, User::count(), 'No extra account may be created by invalid submissions.');
    }

    /** @return array<string,array{0:string,1:string}> */
    public static function validPhoneFormats(): array
    {
        return [
            '10 digits starting with 0' => ['0901234567', '+84901234567'],
            '+84 and 9 digits' => ['+84901234567', '+84901234567'],
            '84 and 9 digits' => ['84901234567', '+84901234567'],
            'spaces are ignored' => ['0901 234 567', '+84901234567'],
            'dots are ignored' => ['090.123.4567', '+84901234567'],
            'dashes are ignored' => ['090-123-4567', '+84901234567'],
            'spaced international' => ['+84 901 234 567', '+84901234567'],
        ];
    }

    #[DataProvider('validPhoneFormats')]
    public function test_registration_normalises_valid_vietnamese_phone_numbers(string $input, string $stored): void
    {
        $this->post('/register', $this->registration(['phone' => $input]))->assertSessionHasNoErrors();

        $this->assertSame($stored, User::firstOrFail()->phone);
    }

    /** @return array<string,array{0:string}> */
    public static function invalidPhoneFormats(): array
    {
        return [
            'too short' => ['123'],
            '9 digits' => ['090123456'],
            '11 digits' => ['09012345678'],
            'letters' => ['abcdefghij'],
            '+84 with 8 digits' => ['+8490123456'],
        ];
    }

    #[DataProvider('invalidPhoneFormats')]
    public function test_registration_rejects_invalid_phone_numbers(string $phone): void
    {
        $this->post('/register', $this->registration(['phone' => $phone]))->assertSessionHasErrors('phone');

        $this->assertSame(0, User::count());
    }

    public function test_registration_address_is_optional(): void
    {
        $this->post('/register', $this->registration(['address' => '']))->assertSessionHasNoErrors();

        $this->assertNull(User::firstOrFail()->address);
    }

    public function test_phone_number_can_be_shared_between_accounts(): void
    {
        User::factory()->create(['phone' => '+84901234567']);

        $this->post('/register', $this->registration(['email' => 'new@example.com', 'phone' => '0901 234 567']))
            ->assertSessionHasNoErrors();
        $this->assertSame(2, User::count());
    }

    public function test_guest_cart_survives_registration_and_switches_to_member_prices(): void
    {
        $p = $this->book();
        $this->inCart($p, 2);

        $this->post('/register', $this->registration());

        $this->assertSame([$p->id => 2], session('cart'));
        $this->get('/cart')->assertSee(Money::vnd(180000))->assertDontSee(Money::vnd(200000));
    }

    public function test_logged_in_member_is_redirected_away_from_register_and_login(): void
    {
        $user = $this->member();

        $this->actingAs($user)->get('/register')->assertRedirect();
        $this->get('/login')->assertRedirect();
    }

    // =====================================================================
    // 2b. Login / logout
    // =====================================================================

    public function test_login_page_is_available_to_guests(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_member_can_log_in_and_log_out(): void
    {
        $user = $this->member(['password' => 'Password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password_or_unknown_email(): void
    {
        $user = $this->member(['password' => 'Password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'The email or password is incorrect.']);
        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'Password123'])
            ->assertSessionHasErrors(['email' => 'The email or password is incorrect.']);
        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);
        $this->post('/login', ['email' => 'not-an-email', 'password' => 'x'])->assertSessionHasErrors('email');
    }

    public function test_guest_is_redirected_to_login_from_member_pages(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/orders')->assertRedirect('/login');
        $this->put('/profile', [])->assertRedirect('/login');
    }

    public function test_login_returns_to_the_page_the_guest_originally_wanted(): void
    {
        $user = $this->member(['password' => 'Password123']);

        $this->get('/profile')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect('/profile');
    }

    public function test_guest_cart_is_kept_when_logging_in_and_prices_become_member_prices(): void
    {
        $user = $this->member(['password' => 'Password123']);
        $p = $this->book();
        $this->inCart($p);
        $this->get('/cart')->assertSee(Money::vnd(100000));

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123']);

        $this->assertSame([$p->id => 1], session('cart'));
        $this->get('/cart')->assertSee(Money::vnd(90000))->assertDontSee(Money::vnd(100000));
    }

    public function test_logout_clears_cart_and_coupon(): void
    {
        $user = $this->member();
        $this->coupon(['min_order_value' => 0]);
        $p = $this->book();
        $this->actingAs($user)->inCart($p);
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();

        $this->post('/logout');

        $this->assertEmpty(session('cart', []));
        $this->assertNull(session('coupon_code'));
    }

    public function test_login_is_rate_limited(): void
    {
        $user = $this->member(['password' => 'Password123']);

        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertStatus(302);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertStatus(429);
        $this->assertGuest();
    }

    // =====================================================================
    // 2c. Profile
    // =====================================================================

    public function test_member_sees_the_current_profile_data(): void
    {
        $user = $this->member(['name' => 'Lan Nguyen', 'email' => 'lan@example.com']);

        $this->actingAs($user)->get('/profile')->assertOk()->assertSee('Lan Nguyen')->assertSee('lan@example.com');
    }

    public function test_member_can_update_profile_and_phone_is_normalised(): void
    {
        $user = $this->member();

        $this->actingAs($user)->put('/profile', [
            'name' => 'New Name', 'email' => 'new@example.com', 'phone' => '0911 222 333', 'address' => '1 Le Loi, District 1, HCMC',
        ])->assertRedirect('/profile')->assertSessionHas('success', 'Your profile has been updated.');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('+84911222333', $user->phone);
        $this->assertSame('1 Le Loi, District 1, HCMC', $user->address);
    }

    public function test_profile_email_must_be_unique_but_member_may_keep_their_own(): void
    {
        $other = $this->member();
        $user = $this->member();
        $payload = ['name' => 'X', 'email' => $other->email, 'phone' => '0911222333', 'address' => ''];

        $this->actingAs($user)->put('/profile', $payload)->assertSessionHasErrors('email');
        $this->put('/profile', ['email' => $user->email] + $payload)->assertSessionHasNoErrors();
    }

    public function test_profile_validation(): void
    {
        $user = $this->member();
        $valid = ['name' => 'Lan', 'email' => $user->email, 'phone' => '0911222333', 'address' => ''];

        $this->actingAs($user)->put('/profile', ['name' => ''] + $valid)->assertSessionHasErrors('name');
        $this->put('/profile', ['phone' => '12345'] + $valid)->assertSessionHasErrors('phone');
        $this->put('/profile', ['email' => 'bad'] + $valid)->assertSessionHasErrors('email');
    }

    public function test_profile_update_cannot_change_the_role(): void
    {
        $user = $this->member();

        $this->actingAs($user)->put('/profile', [
            'name' => 'Lan', 'email' => $user->email, 'phone' => '0911222333', 'address' => '', 'role' => 'admin',
        ]);

        $this->assertSame('customer', $user->fresh()->role);
    }

    // =====================================================================
    // 2d. Order history
    // =====================================================================

    public function test_order_history_shows_only_the_members_own_orders(): void
    {
        $lan = $this->member();
        $mine = Order::factory()->for($lan)->create();
        $theirs = Order::factory()->for($this->member())->create();
        $guestOrder = Order::factory()->create(['user_id' => null]);

        $this->actingAs($lan)->get('/orders')->assertOk()
            ->assertSee($mine->reference)
            ->assertDontSee($theirs->reference)
            ->assertDontSee($guestOrder->reference);
    }

    public function test_order_history_is_empty_for_a_new_member(): void
    {
        $this->actingAs($this->member())->get('/orders')->assertOk()->assertSee('You have not placed any orders yet.');
    }

    public function test_order_history_lists_newest_first_with_total_and_status(): void
    {
        $lan = $this->member();
        $old = Order::factory()->for($lan)->create(['status' => Order::STATUS_COMPLETED]);
        $new = Order::factory()->for($lan)->create(['subtotal' => 200000, 'discount' => 25000, 'delivery_fee' => 30000]);

        $this->actingAs($lan)->get('/orders')
            ->assertSeeInOrder([$new->reference, $old->reference])
            ->assertSee(Money::vnd(205000))
            ->assertSee('Completed')
            ->assertSee('Pending');
    }

    public function test_order_history_is_paginated_by_ten(): void
    {
        $lan = $this->member();
        $orders = collect(range(1, 12))->map(fn () => Order::factory()->for($lan)->create());

        $this->actingAs($lan)->get('/orders')
            ->assertSee($orders->last()->reference)
            ->assertDontSee($orders->first()->reference);
        $this->get('/orders?page=2')->assertSee($orders->first()->reference);
    }

    // =====================================================================
    // 3a. Cart
    // =====================================================================

    public function test_guest_can_add_update_and_remove_items(): void
    {
        $p = $this->book();

        $this->post("/cart/add/{$p->id}", ['quantity' => 2])->assertSessionHas('success');
        $this->assertSame([$p->id => 2], session('cart'));

        $this->patch("/cart/items/{$p->id}", ['quantity' => 3])->assertSessionHas('success', 'Cart updated.');
        $this->assertSame([$p->id => 3], session('cart'));

        $this->delete("/cart/items/{$p->id}")->assertSessionHas('success', 'Item removed from your cart.');
        $this->assertSame([], session('cart'));
    }

    public function test_adding_the_same_book_twice_accumulates_the_quantity(): void
    {
        $p = $this->book();
        $this->inCart($p);
        $this->inCart($p);

        $this->assertSame([$p->id => 2], session('cart'));
    }

    public function test_adding_defaults_to_one_copy(): void
    {
        $p = $this->book();
        $this->post("/cart/add/{$p->id}");

        $this->assertSame([$p->id => 1], session('cart'));
    }

    public function test_updating_the_quantity_to_zero_removes_the_item(): void
    {
        $p = $this->book();
        $this->inCart($p, 2);

        $this->patch("/cart/items/{$p->id}", ['quantity' => 0]);

        $this->assertSame([], session('cart'));
    }

    public function test_quantity_cannot_exceed_stock(): void
    {
        $p = $this->book(['stock' => 3]);

        $this->post("/cart/add/{$p->id}", ['quantity' => 4])->assertSessionHas('error');
        $this->assertEmpty(session('cart', []));

        $this->inCart($p, 3);
        $this->post("/cart/add/{$p->id}")->assertSessionHas('error'); // 3 + 1 > 3
        $this->patch("/cart/items/{$p->id}", ['quantity' => 4])->assertSessionHas('error');
        $this->assertSame([$p->id => 3], session('cart'));
    }

    public function test_quantity_cannot_exceed_the_per_item_limit(): void
    {
        $p = $this->book(['stock' => 50]);

        $this->post("/cart/add/{$p->id}", ['quantity' => 21])
            ->assertSessionHas('error', 'You can order at most 20 copies of the same book.');
        $this->assertEmpty(session('cart', []));

        $this->inCart($p, 20);
        $this->assertSame([$p->id => 20], session('cart'));
    }

    public function test_out_of_stock_and_inactive_books_cannot_be_added(): void
    {
        $out = Product::factory()->outOfStock()->create();
        $inactive = Product::factory()->inactive()->create();

        $this->post("/cart/add/{$out->id}")->assertSessionHas('error');
        $this->post("/cart/add/{$inactive->id}")->assertSessionHas('error');
        $this->assertEmpty(session('cart', []));
    }

    public function test_invalid_quantities_are_rejected(): void
    {
        $p = $this->book();

        foreach ([0, -1, 'abc', 100] as $bad) {
            $this->post("/cart/add/{$p->id}", ['quantity' => $bad])->assertSessionHasErrors('quantity');
        }
        $this->patch("/cart/items/{$p->id}", ['quantity' => -1])->assertSessionHasErrors('quantity');
        $this->patch("/cart/items/{$p->id}", [])->assertSessionHasErrors('quantity');
        $this->assertEmpty(session('cart', []));
    }

    public function test_adding_an_unknown_book_returns_404(): void
    {
        $this->post('/cart/add/999999')->assertNotFound();
    }

    public function test_empty_cart_page_shows_the_empty_state(): void
    {
        $this->get('/cart')->assertOk()->assertSee('Your cart is empty.');
    }

    public function test_a_book_deactivated_after_adding_is_dropped_from_the_cart(): void
    {
        $p = $this->book();
        $this->inCart($p);
        $p->update(['status' => 'inactive']);

        $this->get('/cart')->assertOk()->assertSee('Your cart is empty.');
        $this->assertEmpty(session('cart', []));
    }

    public function test_ajax_cart_endpoints_return_the_formatted_summary(): void
    {
        $p = $this->book();

        $this->postJson("/cart/add/{$p->id}")
            ->assertOk()->assertJsonPath('count', 1)->assertJsonStructure(['message', 'subtotal', 'delivery_fee', 'total', 'lines']);

        $this->patchJson("/cart/items/{$p->id}", ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('subtotal', Money::vnd(200000))
            ->assertJsonPath('delivery_fee', Money::vnd(30000))
            ->assertJsonPath('total', Money::vnd(230000))
            ->assertJsonPath("lines.{$p->id}.line_total", Money::vnd(200000));

        $this->deleteJson("/cart/items/{$p->id}")->assertOk()->assertJsonPath('count', 0);
    }

    public function test_ajax_errors_use_status_422_with_a_customer_safe_message(): void
    {
        $p = $this->book(['stock' => 2]);
        $out = Product::factory()->outOfStock()->create();

        $this->postJson("/cart/add/{$out->id}")->assertStatus(422)->assertJsonStructure(['message']);
        $this->inCart($p);
        $this->patchJson("/cart/items/{$p->id}", ['quantity' => 3])
            ->assertStatus(422)
            ->assertJsonPath('message', "Only 2 copies of \"{$p->name}\" are in stock.");
    }

    // =====================================================================
    // 3b. Guest / member pricing and delivery fee
    // =====================================================================

    public function test_product_price_depends_on_who_is_buying(): void
    {
        $product = (new Product)->forceFill(['guest_price' => 100000, 'member_price' => 90000]);

        $this->assertSame(100000, $product->priceFor(null));
        $this->assertSame(90000, $product->priceFor($this->member()));
    }

    public function test_guest_sees_guest_price_and_member_sees_member_price(): void
    {
        $p = $this->book();
        $this->inCart($p, 2);

        $this->get('/cart')->assertSee(Money::vnd(200000))->assertSee('(guest price)');

        $this->actingAs($this->member())->get('/cart')
            ->assertSee(Money::vnd(180000))->assertSee('(member price)')->assertDontSee(Money::vnd(200000));
    }

    public function test_guest_is_told_how_much_member_prices_would_save(): void
    {
        $p = $this->book();
        $this->inCart($p, 2); // (100.000 - 90.000) x 2

        $this->get('/cart')->assertSee('to pay member prices')->assertSee(Money::vnd(20000))->assertSee('No account needed');
        $this->actingAs($this->member())->get('/cart')->assertDontSee('to pay member prices');
    }

    public function test_delivery_fee_is_fixed_for_a_non_empty_cart_and_zero_for_an_empty_one(): void
    {
        $this->deleteJson('/cart/coupon')->assertOk()->assertJsonPath('delivery_fee', Money::vnd(0))->assertJsonPath('total', Money::vnd(0));

        $p = $this->book();
        $this->postJson("/cart/add/{$p->id}")->assertJsonPath('delivery_fee', Money::vnd(30000))->assertJsonPath('total', Money::vnd(130000));
    }

    public function test_delivery_fee_follows_the_configuration(): void
    {
        config(['booknest.delivery_fee' => 45000]);
        $p = $this->book();

        $this->postJson("/cart/add/{$p->id}")->assertJsonPath('delivery_fee', Money::vnd(45000))->assertJsonPath('total', Money::vnd(145000));
    }

    public function test_money_is_formatted_in_whole_vnd(): void
    {
        $this->assertSame('125.000 ₫', Money::vnd(125000));
        $this->assertSame('1.250.000 ₫', Money::vnd(1250000));
        $this->assertSame('0 ₫', Money::vnd(0));
        $this->assertSame('0 ₫', Money::vnd(null));
    }

    public function test_cart_checkout_and_order_all_use_the_same_price(): void
    {
        $user = $this->member();
        $p = $this->book();
        $this->actingAs($user)->inCart($p, 2);

        $this->get('/cart')->assertSee(Money::vnd(180000));
        $this->get('/checkout')->assertSee(Money::vnd(90000))->assertSee(Money::vnd(180000))->assertSee(Money::vnd(210000));

        $this->post('/checkout', $this->delivery());
        $order = Order::with('items')->firstOrFail();
        $this->assertSame(180000, $order->subtotal);
        $this->assertSame(90000, $order->items->first()->price);
        $this->assertSame(210000, $order->total_price);
    }

    public function test_order_keeps_the_price_paid_even_if_the_book_price_changes_later(): void
    {
        $order = $this->placeGuestOrder();
        $order->items->first()->product->update(['guest_price' => 999000, 'member_price' => 888000]);

        $this->get("/orders/{$order->id}")->assertOk()->assertSee(Money::vnd(100000))->assertDontSee(Money::vnd(999000));
        $this->assertSame(100000, $order->items()->first()->price);
    }

    // =====================================================================
    // 4. Coupon validation
    // =====================================================================

    public function test_guest_cannot_use_a_coupon(): void
    {
        $this->coupon();
        $p = $this->book();
        $this->inCart($p, 2);

        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])
            ->assertStatus(422)->assertJsonPath('message', 'Please log in or register to use a discount code.');
        $this->post('/cart/coupon', ['code' => 'SAVE25'])
            ->assertSessionHas('error', 'Please log in or register to use a discount code.');
        $this->assertNull(session('coupon_code'));
        $this->get('/cart')->assertSee('Discount codes are for members.');
    }

    public function test_member_coupon_reduces_the_total_and_is_case_insensitive(): void
    {
        $this->coupon();
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p, 3); // 3 x 90.000 = 270.000

        $this->postJson('/cart/coupon', ['code' => '  save25 '])
            ->assertOk()
            ->assertJsonPath('coupon_code', 'SAVE25')
            ->assertJsonPath('discount', Money::vnd(25000))
            ->assertJsonPath('has_discount', true)
            ->assertJsonPath('total', Money::vnd(275000)); // 270.000 - 25.000 + 30.000
    }

    public function test_coupon_minimum_order_is_checked_against_the_subtotal(): void
    {
        $this->coupon(['min_order_value' => 200000]);
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p, 2); // 180.000 < 200.000

        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This coupon requires a minimum order of '.Money::vnd(200000).'.');
        $this->assertNull(session('coupon_code'));
    }

    public function test_coupon_is_accepted_when_the_subtotal_equals_the_minimum(): void
    {
        $this->coupon(['min_order_value' => 180000]);
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p, 2); // exactly 180.000

        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();
    }

    public function test_unknown_inactive_and_empty_coupons_are_rejected(): void
    {
        Coupon::factory()->inactive()->create(['code' => 'OFF', 'min_order_value' => 0]);
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p, 3);

        $this->postJson('/cart/coupon', ['code' => 'OFF'])->assertStatus(422)->assertJsonPath('message', 'This coupon is no longer active.');
        $this->postJson('/cart/coupon', ['code' => 'NOPE'])->assertStatus(422)->assertJsonPath('message', 'This coupon code does not exist.');
        $this->postJson('/cart/coupon', ['code' => ''])->assertStatus(422)->assertJsonValidationErrors('code');
        $this->postJson('/cart/coupon', ['code' => str_repeat('A', 51)])->assertStatus(422)->assertJsonValidationErrors('code');
        $this->assertNull(session('coupon_code'));
    }

    public function test_discount_never_exceeds_the_subtotal(): void
    {
        $this->coupon(['code' => 'BIG', 'discount_value' => 500000, 'min_order_value' => 0]);
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p); // 90.000

        $this->postJson('/cart/coupon', ['code' => 'BIG'])->assertOk()
            ->assertJsonPath('discount', Money::vnd(90000))
            ->assertJsonPath('total', Money::vnd(30000)); // only the delivery fee is left
    }

    public function test_only_one_coupon_applies_to_an_order(): void
    {
        $this->coupon(['code' => 'FIRST', 'discount_value' => 10000, 'min_order_value' => 0]);
        $this->coupon(['code' => 'SECOND', 'discount_value' => 20000, 'min_order_value' => 0]);
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p);

        $this->postJson('/cart/coupon', ['code' => 'FIRST'])->assertOk();
        $this->postJson('/cart/coupon', ['code' => 'SECOND'])->assertOk()
            ->assertJsonPath('coupon_code', 'SECOND')->assertJsonPath('discount', Money::vnd(20000));
    }

    public function test_coupon_can_be_removed(): void
    {
        $this->coupon(['min_order_value' => 0]);
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p);
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();

        $this->deleteJson('/cart/coupon')->assertOk()
            ->assertJsonPath('has_discount', false)->assertJsonPath('coupon_code', null)->assertJsonPath('total', Money::vnd(120000));
        $this->assertNull(session('coupon_code'));
    }

    public function test_coupon_is_dropped_when_the_cart_falls_below_the_minimum(): void
    {
        $this->coupon();
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p, 3); // 270.000
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();

        $this->patchJson("/cart/items/{$p->id}", ['quantity' => 2]) // 180.000 < 200.000
            ->assertOk()
            ->assertJsonPath('has_discount', false)
            ->assertJsonPath('coupon_code', null)
            ->assertJsonPath('total', Money::vnd(210000));
        $this->assertNull(session('coupon_code'));
    }

    public function test_coupon_is_dropped_with_a_notice_when_it_is_deactivated(): void
    {
        $coupon = $this->coupon(['min_order_value' => 0]);
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p);
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();

        $coupon->update(['is_active' => false]);

        $this->get('/cart')->assertOk()->assertSee('was removed')->assertSee('no longer active');
        $this->assertNull(session('coupon_code'));
    }

    public function test_coupon_can_be_used_only_once_per_member(): void
    {
        $coupon = $this->coupon(['min_order_value' => 0]);
        $lan = $this->member();
        $other = $this->member();
        $p = $this->book();
        Order::factory()->for($lan)->create(['coupon_id' => $coupon->id]);

        $this->actingAs($lan)->inCart($p);
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertStatus(422)->assertJsonPath('message', 'You have already used this coupon.');

        // A different member is not affected.
        $this->actingAs($other)->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();
    }

    public function test_a_cancelled_order_does_not_use_up_the_coupon(): void
    {
        $coupon = $this->coupon(['min_order_value' => 0]);
        $lan = $this->member();
        Order::factory()->for($lan)->create(['coupon_id' => $coupon->id, 'status' => Order::STATUS_CANCELLED]);
        $p = $this->book();

        $this->actingAs($lan)->inCart($p);
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();
    }

    public function test_a_coupon_used_on_a_guest_order_with_the_members_email_counts_as_used(): void
    {
        $coupon = $this->coupon(['min_order_value' => 0]);
        $lan = $this->member(['email' => 'lan@example.com']);
        Order::factory()->create(['user_id' => null, 'guest_email' => 'LAN@example.com', 'coupon_id' => $coupon->id]);
        $p = $this->book();

        $this->actingAs($lan)->inCart($p);
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertStatus(422)->assertJsonPath('message', 'You have already used this coupon.');
    }

    public function test_checkout_blocks_a_coupon_already_used_with_the_delivery_email(): void
    {
        $coupon = $this->coupon(['min_order_value' => 0]);
        Order::factory()->create(['user_id' => null, 'guest_email' => 'minh@example.com', 'coupon_id' => $coupon->id]);
        $p = $this->book(['stock' => 5]);

        $this->actingAs($this->member())->inCart($p, 2);
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();

        $this->post('/checkout', $this->delivery(['email' => 'minh@example.com']))
            ->assertRedirect('/cart')->assertSessionHas('error');

        $this->assertSame(1, Order::count(), 'No new order may be created.');
        $this->assertSame(5, $p->fresh()->stock, 'Stock must stay untouched.');
        $this->assertNull(session('coupon_code'), 'The used coupon must be removed.');
    }

    // =====================================================================
    // 5a. Checkout (guest and member)
    // =====================================================================

    public function test_guest_checkout_creates_a_pending_order_with_guest_prices(): void
    {
        $p = $this->book();
        $this->inCart($p, 2);

        $response = $this->post('/checkout', $this->delivery());

        $order = Order::with('items')->firstOrFail();
        $response->assertRedirect("/orders/{$order->id}/confirmation")->assertSessionHas('success', 'Thank you! Your order has been placed.');
        $this->assertNull($order->user_id);
        $this->assertSame('Minh Tran', $order->guest_name);
        $this->assertSame('minh@example.com', $order->guest_email);
        $this->assertSame('+84987654321', $order->guest_phone);
        $this->assertSame(200000, $order->subtotal);
        $this->assertSame(0, $order->discount);
        $this->assertSame(30000, $order->delivery_fee);
        $this->assertSame(230000, $order->total_price);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame(100000, $order->items->first()->price);
        $this->assertSame(2, $order->items->first()->quantity);
    }

    public function test_member_checkout_uses_member_prices_and_links_the_order_to_the_member(): void
    {
        $user = $this->member();
        $p = $this->book();
        $this->actingAs($user)->inCart($p, 2);

        $this->post('/checkout', $this->delivery());

        $order = Order::with('items')->firstOrFail();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(180000, $order->subtotal);
        $this->assertSame(90000, $order->items->first()->price);
        $this->assertSame(210000, $order->total_price);
    }

    public function test_checkout_decrements_stock_and_clears_cart_and_coupon(): void
    {
        $this->coupon(['min_order_value' => 0]);
        $p = $this->book(['stock' => 5]);
        $this->actingAs($this->member())->inCart($p, 2);
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();

        $this->post('/checkout', $this->delivery());

        $this->assertSame(3, $p->fresh()->stock);
        $this->assertEmpty(session('cart', []));
        $this->assertNull(session('coupon_code'));
    }

    public function test_checkout_with_a_coupon_stores_the_discount_on_the_order(): void
    {
        $coupon = $this->coupon();
        $p = $this->book();
        $this->actingAs($this->member())->inCart($p, 3); // 270.000
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();

        $this->post('/checkout', $this->delivery());

        $order = Order::firstOrFail();
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame(270000, $order->subtotal);
        $this->assertSame(25000, $order->discount);
        $this->assertSame(275000, $order->total_price);
    }

    public function test_checkout_page_for_a_member_is_prefilled_from_the_profile(): void
    {
        $user = $this->member(['name' => 'Lan Nguyen', 'phone' => '0901234567', 'address' => '12 Nguyen Van Bao, Go Vap']);
        $p = $this->book();
        $this->actingAs($user)->inCart($p);

        $this->get('/checkout')->assertOk()
            ->assertSee('Lan Nguyen')->assertSee('0901234567')->assertSee('12 Nguyen Van Bao, Go Vap')
            ->assertSee(Money::vnd(90000))->assertSee(Money::vnd(30000))->assertSee(Money::vnd(120000))
            ->assertDontSee('Checking out as a');
    }

    public function test_checkout_page_for_a_guest_asks_only_for_delivery_details(): void
    {
        $p = $this->book();
        $this->inCart($p);

        $this->get('/checkout')->assertOk()
            ->assertSee('Checking out as a')->assertSee('Delivery information')
            ->assertSee(Money::vnd(100000))->assertSee(Money::vnd(130000));
    }

    public function test_member_may_deliver_to_different_details_than_the_profile(): void
    {
        $user = $this->member();
        $p = $this->book();
        $this->actingAs($user)->inCart($p);

        $this->post('/checkout', $this->delivery(['name' => 'Present Receiver', 'email' => 'receiver@example.com']));

        $order = Order::firstOrFail();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('Present Receiver', $order->guest_name);
        $this->assertSame('receiver@example.com', $order->guest_email);
    }

    public function test_checkout_validates_the_delivery_form(): void
    {
        $p = $this->book();
        $this->inCart($p);

        $this->post('/checkout', $this->delivery(['name' => '', 'email' => 'bad', 'phone' => '12', 'address' => 'short', 'payment_method' => 'bitcoin']))
            ->assertSessionHasErrors(['name', 'email', 'phone', 'address', 'payment_method']);
        $this->post('/checkout', $this->delivery(['payment_method' => '']))->assertSessionHasErrors('payment_method');
        $this->post('/checkout', $this->delivery(['address' => '123456789']))
            ->assertSessionHasErrors(['address' => 'Please enter a full delivery address (house number, street, ward, city).']);
        $this->post('/checkout', $this->delivery(['phone' => '090123456']))->assertSessionHasErrors('phone');
        $this->assertSame(0, Order::count());
        $this->assertNotEmpty(session('cart'), 'A failed checkout must not empty the cart.');
    }

    #[DataProvider('validPhoneFormats')]
    public function test_checkout_normalises_the_phone_number(string $input, string $stored): void
    {
        $p = $this->book();
        $this->inCart($p);

        $this->post('/checkout', $this->delivery(['phone' => $input]))->assertSessionHasNoErrors();

        $this->assertSame($stored, Order::firstOrFail()->guest_phone);
    }

    public function test_empty_cart_cannot_check_out(): void
    {
        $this->get('/checkout')->assertRedirect('/cart')->assertSessionHas('error');
        $this->post('/checkout', $this->delivery())->assertRedirect('/cart');
        $this->assertSame(0, Order::count());
    }

    public function test_checkout_rejects_stock_that_disappeared_after_adding(): void
    {
        $p = $this->book(['stock' => 5]);
        $this->inCart($p, 4);
        $p->update(['stock' => 2]); // someone else bought copies meanwhile

        $this->get('/checkout')->assertRedirect('/cart')->assertSessionHas('error');
        $this->post('/checkout', $this->delivery())->assertRedirect('/cart')->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(2, $p->fresh()->stock);
    }

    public function test_last_copy_cannot_be_bought_twice(): void
    {
        $p = $this->book(['stock' => 1]);
        $this->inCart($p);
        $this->post('/checkout', $this->delivery())->assertSessionHasNoErrors();
        $this->assertSame(0, $p->fresh()->stock);

        $this->post("/cart/add/{$p->id}")->assertSessionHas('error');
    }

    public function test_checkout_ignores_prices_sent_by_the_browser(): void
    {
        $p = $this->book();
        $this->inCart($p);

        $this->post('/checkout', $this->delivery(['subtotal' => 1, 'discount' => 99999, 'delivery_fee' => 0, 'total' => 1, 'status' => 'Completed', 'payment_status' => 'Paid']));

        $order = Order::firstOrFail();
        $this->assertSame(100000, $order->subtotal);
        $this->assertSame(0, $order->discount);
        $this->assertSame(30000, $order->delivery_fee);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame(Order::PAYMENT_UNPAID, $order->payment_status);
    }

    // =====================================================================
    // 5b. Simulated payment
    // =====================================================================

    /** @return array<string,array{0:string,1:string}> */
    public static function paymentMethods(): array
    {
        return [
            'cash on delivery' => ['cod', 'Unpaid'],
            'card (simulated)' => ['card', 'Paid'],
            'e-wallet (simulated)' => ['ewallet', 'Paid'],
        ];
    }

    #[DataProvider('paymentMethods')]
    public function test_payment_service_marks_the_order_paid_or_unpaid(string $method, string $status): void
    {
        $this->assertSame($status, (new PaymentService)->charge($method, 230000));
    }

    #[DataProvider('paymentMethods')]
    public function test_checkout_stores_the_payment_method_and_status(string $method, string $status): void
    {
        $order = $this->placeGuestOrder(['payment_method' => $method]);

        $this->assertSame($method, $order->payment_method);
        $this->assertSame($status, $order->payment_status);
    }

    public function test_confirmation_explains_the_payment_state(): void
    {
        $cod = $this->placeGuestOrder(['payment_method' => 'cod']);
        $this->get("/orders/{$cod->id}/confirmation")
            ->assertSee('Cash on delivery (COD)')->assertSee('Unpaid')->assertSee('Pay the courier when your order arrives.');

        $card = $this->placeGuestOrder(['payment_method' => 'card']);
        $this->get("/orders/{$card->id}/confirmation")
            ->assertSee('Credit / debit card (simulated)')->assertSee('Paid')->assertDontSee('Pay the courier');
    }

    // =====================================================================
    // 6a. Order confirmation and customer-side order status
    // =====================================================================

    public function test_order_reference_and_total_are_derived_from_the_order(): void
    {
        $order = (new Order)->forceFill(['id' => 12, 'subtotal' => 200000, 'discount' => 25000, 'delivery_fee' => 30000]);
        $this->assertSame('BN-000012', $order->reference);
        $this->assertSame(205000, $order->total_price);

        $huge = (new Order)->forceFill(['id' => 1, 'subtotal' => 100000, 'discount' => 500000, 'delivery_fee' => 30000]);
        $this->assertSame(30000, $huge->total_price, 'A discount can never make the goods cost less than 0.');
    }

    public function test_only_pending_orders_are_cancellable(): void
    {
        foreach (Order::STATUSES as $status) {
            $order = (new Order)->forceFill(['status' => $status]);
            $this->assertSame($status === Order::STATUS_PENDING, $order->isCancellable(), $status);
        }
    }

    public function test_confirmation_page_shows_the_order_to_the_guest_who_placed_it(): void
    {
        $order = $this->placeGuestOrder();

        $this->get("/orders/{$order->id}/confirmation")->assertOk()
            ->assertSee('Thank you, Minh Tran!')
            ->assertSee($order->reference)
            ->assertSee('look it up any time')
            ->assertSee('25 Le Loi, District 1, Ho Chi Minh City')
            ->assertSee(Money::vnd(230000));
        $this->assertSame([$order->id], session('viewable_orders'));
    }

    public function test_confirmation_page_for_a_member_does_not_ask_to_use_order_tracking(): void
    {
        $user = $this->member();
        $p = $this->book();
        $this->actingAs($user)->inCart($p);
        $this->post('/checkout', $this->delivery());
        $order = Order::firstOrFail();

        $this->get("/orders/{$order->id}/confirmation")->assertOk()
            ->assertSee($order->reference)->assertDontSee('look it up any time');
    }

    public function test_order_page_shows_items_discount_delivery_fee_and_total(): void
    {
        $this->coupon();
        $p = $this->book(['name' => 'Clean Code Test Copy']);
        $this->actingAs($this->member())->inCart($p, 3);
        $this->postJson('/cart/coupon', ['code' => 'SAVE25'])->assertOk();
        $this->post('/checkout', $this->delivery());
        $order = Order::firstOrFail();

        $this->get("/orders/{$order->id}")->assertOk()
            ->assertSee('Clean Code Test Copy')
            ->assertSee(Money::vnd(90000))   // unit price
            ->assertSee(Money::vnd(270000))  // subtotal / line total
            ->assertSee('Discount')->assertSee('SAVE25')->assertSee(Money::vnd(25000))
            ->assertSee(Money::vnd(30000))   // delivery fee
            ->assertSee(Money::vnd(275000)); // total
    }

    public function test_every_status_is_shown_to_the_customer(): void
    {
        $lan = $this->member();

        foreach (Order::STATUSES as $status) {
            $order = Order::factory()->for($lan)->create(['status' => $status]);
            $this->actingAs($lan)->get("/orders/{$order->id}")->assertOk()->assertSee($status);
        }
    }

    public function test_member_sees_the_status_updated_by_the_admin(): void
    {
        $lan = $this->member();
        $order = Order::factory()->for($lan)->create(['status' => Order::STATUS_PENDING]);

        $this->actingAs($lan)->get("/orders/{$order->id}")->assertSee('Pending')->assertSee('Cancel order');

        $order->update(['status' => Order::STATUS_SHIPPING]); // what the admin area does
        $this->get("/orders/{$order->id}")->assertSee('Shipping')->assertDontSee('Cancel order');
        $this->get('/orders')->assertSee('Shipping');
    }

    public function test_cancelled_order_shows_a_cancelled_notice(): void
    {
        $lan = $this->member();
        $order = Order::factory()->for($lan)->create(['status' => Order::STATUS_CANCELLED]);

        $this->actingAs($lan)->get("/orders/{$order->id}")->assertSee('This order was cancelled.')->assertDontSee('Cancel order');
    }

    public function test_order_pages_are_private(): void
    {
        $lan = $this->member();
        $mine = Order::factory()->for($lan)->create();
        $theirs = Order::factory()->for($this->member())->create();
        $guestOrder = Order::factory()->create(['user_id' => null]);

        // A member opens only their own orders.
        $this->actingAs($lan)->get("/orders/{$mine->id}")->assertOk();
        $this->get("/orders/{$mine->id}/confirmation")->assertOk();
        $this->get("/orders/{$theirs->id}")->assertNotFound();
        $this->get("/orders/{$theirs->id}/confirmation")->assertNotFound();
        $this->get("/orders/{$guestOrder->id}")->assertNotFound();

        // A visitor with no session access gets 404 (not a login redirect, so references cannot be probed).
        $this->post('/logout');
        $this->flushSession();
        $this->get("/orders/{$mine->id}")->assertNotFound();
    }

    public function test_order_id_must_be_numeric(): void
    {
        $this->get('/orders/abc')->assertNotFound();
        $this->get('/orders/abc/confirmation')->assertNotFound();
    }

    // =====================================================================
    // 6b. Guest order tracking
    // =====================================================================

    public function test_track_page_is_available_to_everyone(): void
    {
        $this->get('/orders/track')->assertOk()->assertSee('Track your order');
        $this->actingAs($this->member())->get('/orders/track')->assertOk()->assertSee('My orders');
    }

    public function test_guest_can_track_an_order_with_reference_and_email(): void
    {
        $order = Order::factory()->create(['user_id' => null, 'guest_email' => 'minh@example.com', 'status' => Order::STATUS_CONFIRMED]);

        $this->get("/orders/{$order->id}")->assertNotFound(); // no access yet

        $this->post('/orders/track', ['reference' => $order->reference, 'email' => 'minh@example.com'])
            ->assertRedirect("/orders/{$order->id}");
        $this->get("/orders/{$order->id}")->assertOk()->assertSee('Confirmed');
    }

    public function test_tracking_accepts_flexible_references_and_any_email_case(): void
    {
        $order = Order::factory()->create(['user_id' => null, 'guest_email' => 'minh@example.com']);

        foreach ([$order->reference, strtolower($order->reference), 'bn-'.$order->id, (string) $order->id] as $reference) {
            $this->post('/orders/track', ['reference' => $reference, 'email' => 'MINH@Example.com'])
                ->assertRedirect("/orders/{$order->id}");
        }
    }

    public function test_tracking_fails_with_the_same_message_for_wrong_email_or_unknown_order(): void
    {
        $order = Order::factory()->create(['user_id' => null, 'guest_email' => 'minh@example.com']);
        $message = 'No order matches that reference and email.';

        $this->post('/orders/track', ['reference' => $order->reference, 'email' => 'other@example.com'])->assertSessionHas('error', $message);
        $this->post('/orders/track', ['reference' => 'BN-999999', 'email' => 'minh@example.com'])->assertSessionHas('error', $message);
        $this->post('/orders/track', ['reference' => 'wrong', 'email' => 'minh@example.com'])->assertSessionHas('error', $message);
        $this->post('/orders/track', ['reference' => 'BN-000000', 'email' => 'minh@example.com'])->assertSessionHas('error', $message);
        $this->get("/orders/{$order->id}")->assertNotFound();
    }

    public function test_tracking_validates_its_form(): void
    {
        $this->post('/orders/track', [])->assertSessionHasErrors(['reference', 'email']);
        $this->post('/orders/track', ['reference' => 'BN-000001', 'email' => 'not-an-email'])->assertSessionHasErrors('email');
    }

    public function test_tracking_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/orders/track', ['reference' => 'BN-999999', 'email' => 'a@b.com'])->assertStatus(302);
        }

        $this->post('/orders/track', ['reference' => 'BN-999999', 'email' => 'a@b.com'])->assertStatus(429);
    }

    // =====================================================================
    // 6c. Cancelling an order
    // =====================================================================

    public function test_member_can_cancel_a_pending_order_and_stock_is_restored(): void
    {
        $user = $this->member();
        $p = $this->book(['stock' => 5]);
        $this->actingAs($user)->inCart($p, 2);
        $this->post('/checkout', $this->delivery());
        $order = Order::firstOrFail();
        $this->assertSame(3, $p->fresh()->stock);

        $this->post("/orders/{$order->id}/cancel")
            ->assertRedirect("/orders/{$order->id}")->assertSessionHas('success', 'Your order has been cancelled.');

        $order->refresh();
        $this->assertSame(Order::STATUS_CANCELLED, $order->status);
        $this->assertSame(Order::PAYMENT_UNPAID, $order->payment_status);
        $this->assertSame(5, $p->fresh()->stock);
    }

    public function test_cancelling_a_paid_order_marks_it_refunded(): void
    {
        $order = $this->placeGuestOrder(['payment_method' => 'ewallet']);
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);

        $this->post("/orders/{$order->id}/cancel");

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(Order::PAYMENT_REFUNDED, $order->fresh()->payment_status);
        $this->get("/orders/{$order->id}")->assertSee('Refunded');
    }

    public function test_guest_can_cancel_the_order_they_placed(): void
    {
        $order = $this->placeGuestOrder();

        $this->post("/orders/{$order->id}/cancel")->assertSessionHas('success');

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_orders_cannot_be_cancelled_once_they_are_confirmed_or_later(): void
    {
        $lan = $this->member();

        foreach ([Order::STATUS_CONFIRMED, Order::STATUS_SHIPPING, Order::STATUS_COMPLETED] as $status) {
            $order = Order::factory()->for($lan)->create(['status' => $status]);

            $this->actingAs($lan)->post("/orders/{$order->id}/cancel")
                ->assertRedirect("/orders/{$order->id}")
                ->assertSessionHas('error', 'This order can no longer be cancelled because it is already '.strtolower($status).'.');
            $this->assertSame($status, $order->fresh()->status);
        }
    }

    public function test_cancelling_twice_does_not_restore_stock_twice(): void
    {
        $lan = $this->member();
        $order = Order::factory()->for($lan)->create();
        $product = $order->items()->firstOrFail()->product; // factory stock = 10

        $this->actingAs($lan)->post("/orders/{$order->id}/cancel")->assertSessionHas('success');
        $this->post("/orders/{$order->id}/cancel")->assertSessionHas('error');

        $this->assertSame(11, $product->fresh()->stock);
    }

    public function test_nobody_else_can_cancel_someones_order(): void
    {
        $order = Order::factory()->for($this->member())->create();
        $guestOrder = Order::factory()->create(['user_id' => null]);

        $this->actingAs($this->member())->post("/orders/{$order->id}/cancel")->assertNotFound();
        $this->post("/orders/{$guestOrder->id}/cancel")->assertNotFound();

        $this->post('/logout');
        $this->flushSession();
        $this->post("/orders/{$order->id}/cancel")->assertNotFound();
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertSame(Order::STATUS_PENDING, $guestOrder->fresh()->status);
    }

    // =====================================================================
    // 7. End-to-end journeys
    // =====================================================================

    public function test_journey_guest_buys_and_later_tracks_the_order(): void
    {
        $p = $this->book(['stock' => 5]);

        // browse -> cart -> guest price -> checkout (COD)
        $this->inCart($p, 2);
        $this->get('/cart')->assertSee('(guest price)')->assertSee(Money::vnd(230000));
        $this->get('/checkout')->assertOk();
        $this->post('/checkout', $this->delivery())->assertRedirect();
        $order = Order::firstOrFail();

        // confirmation
        $this->get("/orders/{$order->id}/confirmation")->assertOk()->assertSee($order->reference);

        // the guest comes back later in a new browser session
        $this->flushSession();
        $this->get("/orders/{$order->id}")->assertNotFound();
        $this->post('/orders/track', ['reference' => $order->reference, 'email' => 'minh@example.com'])->assertRedirect("/orders/{$order->id}");
        $this->get("/orders/{$order->id}")->assertOk()->assertSee('Pending');

        // the admin confirms the order -> the customer sees the new status
        $order->update(['status' => Order::STATUS_CONFIRMED]);
        $this->get("/orders/{$order->id}")->assertSee('Confirmed')->assertDontSee('Cancel order');
    }

    public function test_journey_new_member_registers_buys_with_coupon_and_cancels(): void
    {
        $this->coupon(['code' => 'WELCOME25']);
        $p = $this->book(['stock' => 10]);

        // a guest fills the cart, then registers
        $this->inCart($p, 3);
        $this->post('/register', $this->registration())->assertSessionHasNoErrors();
        $this->assertAuthenticated();

        // member price + coupon
        $this->get('/cart')->assertSee('(member price)')->assertSee(Money::vnd(270000));
        $this->postJson('/cart/coupon', ['code' => 'welcome25'])->assertOk()->assertJsonPath('total', Money::vnd(275000));

        // checkout with simulated card payment
        $this->get('/checkout')->assertOk()->assertSee('Lan Nguyen');
        $this->post('/checkout', $this->delivery(['payment_method' => 'card']))->assertRedirect();
        $order = Order::with('items')->firstOrFail();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(7, $p->fresh()->stock);

        // order history and status
        $this->get('/orders')->assertSee($order->reference)->assertSee(Money::vnd(275000))->assertSee('Pending');
        $this->get("/orders/{$order->id}")->assertSee('WELCOME25');

        // the coupon is used up, but a cancelled order frees it again
        $this->inCart($p, 3);
        $this->postJson('/cart/coupon', ['code' => 'WELCOME25'])->assertStatus(422)->assertJsonPath('message', 'You have already used this coupon.');
        $this->post("/orders/{$order->id}/cancel")->assertSessionHas('success');
        $this->assertSame(10, $p->fresh()->stock);
        $this->assertSame(Order::PAYMENT_REFUNDED, $order->fresh()->payment_status);
        $this->postJson('/cart/coupon', ['code' => 'WELCOME25'])->assertOk();
    }
}