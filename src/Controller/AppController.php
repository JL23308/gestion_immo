<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use SwaggerBake\Lib\Attribute\OpenApiSecurity;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
#[OpenApiSecurity(name: 'BearerAuth')]
class AppController extends Controller
{
    /**
     * @var float Request start time
     */
    protected float $requestStartTime;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->response = $this->response->withType('application/json');
        $this->viewBuilder()->setClassName('Json');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);
        
        // Capture request start time
        $this->requestStartTime = microtime(true);
        
        $this->response = $this->response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization, Accept');

        if ($this->request->is('options')) {
            return $this->response;
        }

        return null;
    }

    /**
     * Format response with metadata
     *
     * @param array $data Response data
     * @return array Response with metadata
     */
    protected function formatResponse(array $data): array
    {
        $endTime = microtime(true);
        $startTime = $this->requestStartTime ?? $endTime;
        
        // Add HTTP status code to response if not present
        if (!isset($data['code'])) {
            $data['code'] = $this->response->getStatusCode();
        }
        
        return [
            'response' => $data,
            'metadata' => [
                'timestamp_start' => date('Y-m-d H:i:s', (int)$startTime),
                'timestamp_end' => date('Y-m-d H:i:s', (int)$endTime),
                'execution_time' => round(($endTime - $startTime) * 1000, 2) . 'ms',
            ],
        ];
    }
}
