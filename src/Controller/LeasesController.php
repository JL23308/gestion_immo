<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;

/**
 * Leases Controller (RESTful API)
 *
 * @property \App\Model\Table\LeasesTable $Leases
 */
class LeasesController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * Index method - GET /api/v1/leases.json
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        $cacheKey = 'leases_list_' . md5(serialize($this->request->getQueryParams()));
        $cachedData = Cache::read($cacheKey, 'api');

        if ($cachedData !== null) {
            $response = $cachedData;
        } else {
            $query = $this->Leases->find()->contain(['Apartments', 'Users']);
            
            // Filter by status if provided
            $status = $this->request->getQuery('status');
            if ($status) {
                $query->where(['Leases.status' => $status]);
            }
            
            // Filtrer par appartement
            $apartmentId = $this->request->getQuery('apartment_id');
            if ($apartmentId) {
                $query->where(['Leases.apartment_id' => (int)$apartmentId]);
            }
            
            // Filtrer par utilisateur
            $userId = $this->request->getQuery('user_id');
            if ($userId) {
                $query->where(['Leases.user_id' => (int)$userId]);
            }
            
            // Filtrer par date de début
            $startDate = $this->request->getQuery('start_date');
            if ($startDate) {
                $query->where(['Leases.start_date >=' => $startDate]);
            }
            
            // Filtrer par date de fin
            $endDate = $this->request->getQuery('end_date');
            if ($endDate) {
                $query->where(['Leases.end_date <=' => $endDate]);
            }

            $leases = $this->paginate($query);

            $response = [
                'success' => true,
                'message' => 'List of leases',
                'data' => $leases,
                'code' => 200,
            ];

            Cache::write($cacheKey, $response, 'api');
        }

        // Add pagination info to metadata
        $paging = $this->request->getAttribute('paging')['Leases'] ?? [];
        $formattedResponse = $this->formatResponse($response);
        if (!empty($paging)) {
            $formattedResponse['metadata']['pagination'] = [
                'page' => $paging['page'] ?? 1,
                'limit' => $paging['perPage'] ?? 20,
                'total' => $paging['count'] ?? 0,
                'pages' => $paging['pageCount'] ?? 1,
            ];
        }
        $this->set($formattedResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));

        return null;
    }

    /**
     * View method - GET /api/v1/leases/{id}.json
     *
     * @param string|null $id Lease id.
     * @return \Cake\Http\Response|null
     */
    public function view(?string $id = null): ?Response
    {
        $cacheKey = 'lease_' . $id;
        $cachedData = Cache::read($cacheKey, 'api');

        if ($cachedData !== null) {
            $response = $cachedData;
        } else {
            try {
                $lease = $this->Leases->get($id, contain: ['Apartments', 'Users']);

                $response = [
                    'success' => true,
                    'message' => 'Lease details',
                    'data' => $lease,
                    'code' => 200,
                ];

                Cache::write($cacheKey, $response, 'api');
            } catch (RecordNotFoundException $e) {
                $this->response = $this->response->withStatus(404);
                $response = [
                    'success' => false,
                    'message' => 'Lease not found',
                    'code' => 404,
                ];
            }
        }

        $formattedResponse = $this->formatResponse($response);
        $this->set($formattedResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));

        return null;
    }

    /**
     * Add method - POST /api/v1/leases.json
     *
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $lease = $this->Leases->newEmptyEntity();
        $statusCode = 200;

        if ($this->request->is('post')) {
            $lease = $this->Leases->patchEntity($lease, $this->request->getData());

            if ($this->Leases->save($lease)) {
                $statusCode = 201;
                $response = [
                    'success' => true,
                    'message' => 'Lease created successfully',
                    'data' => $lease,
                    'code' => $statusCode,
                ];

                Cache::clear('api');
            } else {
                $statusCode = 400;
                $response = [
                    'success' => false,
                    'message' => 'Unable to create lease',
                    'errors' => $lease->getErrors(),
                    'code' => $statusCode,
                ];
            }
        } else {
            $statusCode = 405;
            $response = [
                'success' => false,
                'message' => 'Invalid request method. POST required.',
                'code' => $statusCode,
            ];
        }

        $this->response = $this->response->withStatus($statusCode);
        $formattedResponse = $this->formatResponse($response);
        $this->set($formattedResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));

        return null;
    }

    /**
     * Edit method - PUT/PATCH /api/v1/leases/{id}.json
     *
     * @param string|null $id Lease id.
     * @return \Cake\Http\Response|null
     */
    public function edit(?string $id = null): ?Response
    {
        $statusCode = 405;
        $response = ['success' => false, 'message' => 'Invalid method', 'code' => $statusCode];

        try {
            $lease = $this->Leases->get($id);

            if ($this->request->is(['patch', 'post', 'put'])) {
                $lease = $this->Leases->patchEntity($lease, $this->request->getData());

                if ($this->Leases->save($lease)) {
                    $statusCode = 200;
                    $response = [
                        'success' => true,
                        'message' => 'Lease updated successfully',
                        'data' => $lease,
                        'code' => $statusCode,
                    ];

                    Cache::delete('lease_' . $id, 'api');
                    Cache::clear('api');
                } else {
                    $statusCode = 400;
                    $response = [
                        'success' => false,
                        'message' => 'Unable to update lease',
                        'errors' => $lease->getErrors(),
                        'code' => $statusCode,
                    ];
                }
            }
        } catch (RecordNotFoundException $e) {
            $statusCode = 404;
            $response = [
                'success' => false,
                'message' => 'Lease not found',
                'code' => $statusCode,
            ];
        }

        $this->response = $this->response->withStatus($statusCode);
        $formattedResponse = $this->formatResponse($response);
        $this->set($formattedResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));

        return null;
    }

    /**
     * Delete method - DELETE /api/v1/leases/{id}.json
     *
     * @param string|null $id Lease id.
     * @return \Cake\Http\Response|null
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $statusCode = 200;

        try {
            $lease = $this->Leases->get($id);
            if ($this->Leases->delete($lease)) {
                Cache::delete('lease_' . $id, 'api');
                Cache::clear('api');
            }
        } catch (RecordNotFoundException $e) {
            // Idempotent delete: resource already gone, treat as success
        }

        // Always return success for idempotent delete
        $response = [
            'success' => true,
            'message' => 'The lease has been deleted successfully.',
            'code' => $statusCode,
        ];

        $this->response = $this->response->withStatus($statusCode);
        $formattedResponse = $this->formatResponse($response);
        $this->set($formattedResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));

        return null;
    }
}
