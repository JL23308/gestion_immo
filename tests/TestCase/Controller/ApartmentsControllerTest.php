<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ApartmentsController Test Case
 *
 * @link \App\Controller\ApartmentsController
 */
class ApartmentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.apartments',
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
     * Test index method
     *
     * @return void
     * @link \App\Controller\ApartmentsController::index()
     */
    public function testIndex(): void
    {
        $this->get('/api/v1/apartments');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertTrue($response['response']['success']);
        $this->assertArrayHasKey('message', $response['response']);
        $this->assertArrayHasKey('data', $response['response']);
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\ApartmentsController::view()
     */
    public function testView(): void
    {
        $this->get('/api/v1/apartments/1');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertTrue($response['response']['success']);
        $this->assertArrayHasKey('data', $response['response']);
    }

    /**
     * Test view method with invalid id
     *
     * @return void
     */
    public function testViewNotFound(): void
    {
        $this->get('/api/v1/apartments/999');

        $this->assertResponseCode(404);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertFalse($response['response']['success']);
        $this->assertEquals('Apartment not found', $response['response']['message']);
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\ApartmentsController::add()
     */
    public function testAdd(): void
    {
        $data = [
            'rent' => 500,
            'address' => '123 Test Street',
            'booked' => 0,
            'energy_class' => 'A',
            'nb_rooms' => 2,
            'nb_bathrooms' => 1,
            'size' => 50,
            'climat_class' => 'B',
        ];

        $this->post('/api/v1/apartments', $data);

        $this->assertResponseCode(201);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertTrue($response['response']['success']);
        $this->assertEquals('The apartment has been saved.', $response['response']['message']);
        $this->assertArrayHasKey('data', $response['response']);
    }

    /**
     * Test add method with invalid data
     *
     * @return void
     */
    public function testAddInvalid(): void
    {
        $data = [
            'rent' => 'invalid',
        ];

        $this->post('/api/v1/apartments', $data);

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertFalse($response['response']['success']);
        $this->assertArrayHasKey('errors', $response['response']);
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\ApartmentsController::edit()
     */
    public function testEdit(): void
    {
        $data = [
            'rent' => 600,
        ];

        $this->patch('/api/v1/apartments/1', $data);

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('response', $response);
        $this->assertTrue($response['response']['success']);
        $this->assertEquals('The apartment has been saved.', $response['response']['message']);
    }

    /**
     * Test edit method with invalid id
     *
     * @return void
     */
    public function testEditNotFound(): void
    {
        $data = [
            'rent' => 600,
        ];

        $this->patch('/api/v1/apartments/999', $data);

        $this->assertResponseCode(404);
        $this->assertContentType('application/json');
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\ApartmentsController::delete()
     */
    public function testDelete(): void
    {
        $this->delete('/api/v1/apartments/1');

        $this->assertResponseCode(204);
        // No body to check for 204
    }

    /**
     * Test delete method with invalid id (should still return 203)
     *
     * @return void
     */
    public function testDeleteNotFound(): void
    {
        $this->delete('/api/v1/apartments/999');

        $this->assertResponseCode(204);
        // No body to check
    }
}
