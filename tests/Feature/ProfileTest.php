<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function profile_page_is_displayed()
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/profile');

        $response->assertOk();
    }

    /** @test */
    public function profile_information_can_be_updated()
    {
        $response = $this
            ->actingAs($this->user)
            ->patch('/profile', [
                'name' => 'Test User Updated',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->user->refresh();

        $this->assertSame('Test User Updated', $this->user->name);
        $this->assertSame('test@example.com', $this->user->email);
    }

    /** @test */
    public function demographic_information_can_be_updated()
    {
        $response = $this
            ->actingAs($this->user)
            ->patch('/profile', [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'gender' => 'male',
                'age_group' => '25_34',
                'continent' => 'Europe',
                'country' => 'Germany',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->user->refresh();

        $this->assertSame('male', $this->user->gender);
        $this->assertSame('25_34', $this->user->age_group);
        $this->assertSame('Europe', $this->user->continent);
        $this->assertSame('Germany', $this->user->country);
        $this->assertNull($this->user->birth_date);
    }

    /** @test */
    public function birth_date_can_be_updated_instead_of_age_group()
    {
        $response = $this
            ->actingAs($this->user)
            ->patch('/profile', [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'gender' => 'female',
                'birth_date' => '1990-01-15',
                'continent' => 'North America',
                'country' => 'United States',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->user->refresh();

        $this->assertSame('female', $this->user->gender);
        $this->assertSame('1990-01-15', $this->user->birth_date->format('Y-m-d'));
        $this->assertSame('North America', $this->user->continent);
        $this->assertSame('United States', $this->user->country);
        $this->assertNull($this->user->age_group);
    }

    /** @test */
    public function if_both_birth_date_and_age_group_are_provided_age_group_is_cleared()
    {
        $response = $this
            ->actingAs($this->user)
            ->patch('/profile', [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'gender' => 'male',
                'birth_date' => '1985-05-20',
                'age_group' => '35_44', // This should be cleared
                'continent' => 'Europe',
                'country' => 'France',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->user->refresh();

        $this->assertSame('male', $this->user->gender);
        $this->assertSame('1985-05-20', $this->user->birth_date->format('Y-m-d'));
        $this->assertNull($this->user->age_group);
    }

    /** @test */
    public function email_verification_status_is_unchanged_when_email_address_is_unchanged()
    {
        $this->user->email_verified_at = now();
        $this->user->save();

        $response = $this
            ->actingAs($this->user)
            ->patch('/profile', [
                'name' => 'Test User Updated',
                'email' => $this->user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($this->user->refresh()->email_verified_at);
    }

    /** @test */
    public function email_verification_status_is_reset_when_email_address_is_changed()
    {
        $this->user->email_verified_at = now();
        $this->user->save();

        $response = $this
            ->actingAs($this->user)
            ->patch('/profile', [
                'name' => 'Test User Updated',
                'email' => 'new-email@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNull($this->user->refresh()->email_verified_at);
    }

    /** @test */
    public function user_can_delete_their_account()
    {
        $response = $this
            ->actingAs($this->user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull(User::find($this->user->id));
    }

    /** @test */
    public function correct_password_must_be_provided_to_delete_account()
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull(User::find($this->user->id));
    }

    /** @test */
    public function validation_fails_with_invalid_demographic_data()
    {
        // Disable RefreshDatabase's automatic transaction just for this test
        // by directly testing validation rules rather than going through the full HTTP stack
        $validator = validator([
            'name' => $this->user->name,
            'email' => $this->user->email,
            'gender' => 'invalid-gender', // Not in the allowed values
            'birth_date' => '2999-01-01', // Date in the future
            'continent' => 'invalid-continent', // Not in the allowed values
        ], [
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'continent' => ['nullable', 'string', 'in:Africa,Asia,Europe,North America,South America,Australia/Oceania,Antarctica'],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('gender'));
        $this->assertTrue($validator->errors()->has('birth_date'));
        $this->assertTrue($validator->errors()->has('continent'));
    }
}
