<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\UsersController Test Case
 *
 * @uses \App\Controller\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Test register method
     *
     * @return void
     * @uses \App\Controller\UsersController::register()
     */
    public function testRegister(): void
    {
        $data = [
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ];

        $this->post('/api/v1/users/register', json_encode($data));

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertTrue($response['response']['success']);
        $this->assertEquals('User created', $response['response']['message']);
        $this->assertArrayHasKey('data', $response['response']);
    }

    /**
     * Test register with duplicate email
     *
     * @return void
     * @uses \App\Controller\UsersController::register()
     */
    public function testRegisterDuplicateEmail(): void
    {
        $data = [
            'email' => 'user1@example.com',
            'password' => 'password123',
        ];

        $this->post('/api/v1/users/register', json_encode($data));

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertFalse($response['response']['success']);
        $this->assertEquals('Registration failed', $response['response']['message']);
        $this->assertArrayHasKey('errors', $response['response']);
    }

    /**
     * Test register with invalid data
     *
     * @return void
     * @uses \App\Controller\UsersController::register()
     */
    public function testRegisterInvalidData(): void
    {
        $data = [
            'email' => 'not-an-email',
            'password' => '',
        ];

        $this->post('/api/v1/users/register', json_encode($data));

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertFalse($response['response']['success']);
        $this->assertArrayHasKey('errors', $response['response']);
    }

    /**
     * Test login method
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLogin(): void
    {
        $data = [
            'email' => 'user1@example.com',
            'password' => 'password1',
        ];

        $this->post('/api/v1/users/login', json_encode($data));

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertTrue($response['response']['success']);
        $this->assertEquals('Login successful', $response['response']['message']);
        $this->assertArrayHasKey('data', $response['response']);
        $this->assertArrayHasKey('token', $response['response']['data']);
        $this->assertArrayHasKey('user', $response['response']['data']);
    }

    /**
     * Test login with invalid credentials
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLoginInvalidCredentials(): void
    {
        $data = [
            'email' => 'user1@example.com',
            'password' => 'wrongpassword',
        ];

        $this->post('/api/v1/users/login', json_encode($data));

        $this->assertResponseCode(401);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertFalse($response['response']['success']);
        $this->assertEquals('Invalid credentials', $response['response']['message']);
    }

    /**
     * Test login with non-existent user
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLoginNonExistentUser(): void
    {
        $data = [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ];

        $this->post('/api/v1/users/login', json_encode($data));

        $this->assertResponseCode(401);
        $this->assertContentType('application/json');
    }


}
