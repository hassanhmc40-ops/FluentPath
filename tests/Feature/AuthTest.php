<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('register', function () {
    it('registers a new student and returns an API token', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role', 'created_at'],
                'token',
            ])
            ->assertJsonPath('user.role', 'student');

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'student',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        expect($user->role)->toBe(UserRole::Student)
            ->and(Hash::check('secret123', $user->password))->toBeTrue()
            ->and($response->json('token'))->toBeString()->not->toBeEmpty();
    });

    it('rejects a duplicate email with 422', function () {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'taken@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('users', 1);
    });

    it('rejects a mismatched password confirmation with 422', function () {
        $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'different123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('password');
    });

    it('rejects a password shorter than 8 characters with 422', function () {
        $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('password');
    });

    it('rejects a payload missing required fields with 422', function () {
        $this->postJson('/api/register', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });
});

describe('login', function () {
    it('logs in with valid credentials and returns a token', function () {
        $user = User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token'])
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'jane@example.com');
    });

    it('returns 401 (not 500) for an incorrect password', function () {
        User::factory()->create(['email' => 'jane@example.com']);

        $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials.');
    });

    it('returns 401 for an unknown email', function () {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertStatus(401);
    });

    it('rejects a payload missing credentials with 422', function () {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    });
});

describe('me', function () {
    it('returns the authenticated user with a valid token', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertStatus(200)
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email)
            ->assertJsonMissingPath('password');
    });

    it('returns 401 without a token', function () {
        $this->getJson('/api/me')->assertStatus(401);
    });
});

describe('logout', function () {
    it('logs out and deletes the current token', function () {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertStatus(204);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    });

    it('rejects a deleted token with 401', function () {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $user->tokens()->delete();

        $this->withToken($token)->getJson('/api/me')->assertStatus(401);
    });

    it('returns 401 when logging out without a token', function () {
        $this->postJson('/api/logout')->assertStatus(401);
    });
});
