<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;

/**
 * Apartments Controller (API JSON)
 *
 * Routes attendues (voir `config/routes.php`) :
 * - GET    /api/v1/apartments.json
 * - GET    /api/v1/apartments/{id}.json
 * - POST   /api/v1/apartments.json
 * - PUT    /api/v1/apartments/{id}.json
 * - PATCH  /api/v1/apartments/{id}.json
 * - DELETE /api/v1/apartments/{id}.json
 *
 * @property \App\Model\Table\ApartmentsTable $Apartments
 */
class ApartmentsController extends AppController
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

    public function index(): ?Response
    {
        // Try to get from cache
        $cacheKey = 'apartments_list_' . md5(serialize($this->request->getQueryParams()));
        $cachedData = Cache::read($cacheKey, 'api');

        if ($cachedData !== null) {
            $response = $cachedData;
        } else {
            $query = $this->Apartments->find();
            
            // Filtrer par prix min/max
            $minRent = $this->request->getQuery('min_rent');
            if ($minRent) {
                $query->where(['Apartments.rent >=' => (float)$minRent]);
            }
            $maxRent = $this->request->getQuery('max_rent');
            if ($maxRent) {
                $query->where(['Apartments.rent <=' => (float)$maxRent]);
            }
            
            // Filtrer par taille min/max
            $minSize = $this->request->getQuery('min_size');
            if ($minSize) {
                $query->where(['Apartments.size >=' => (float)$minSize]);
            }
            $maxSize = $this->request->getQuery('max_size');
            if ($maxSize) {
                $query->where(['Apartments.size <=' => (float)$maxSize]);
            }
            
            // Filtrer par nombre de pièces
            $nbRooms = $this->request->getQuery('nb_rooms');
            if ($nbRooms) {
                $query->where(['Apartments.nb_rooms >=' => (int)$nbRooms]);
            }
            
            // Filtrer par nombre de salles de bain
            $nbBathrooms = $this->request->getQuery('nb_bathrooms');
            if ($nbBathrooms) {
                $query->where(['Apartments.nb_bathrooms >=' => (int)$nbBathrooms]);
            }
            
            // Filtrer par disponibilité
            $booked = $this->request->getQuery('booked');
            if ($booked !== null) {
                $query->where(['Apartments.booked' => (bool)$booked]);
            }
            
            // Filtrer par classe énergétique
            $energyClass = $this->request->getQuery('energy_class');
            if ($energyClass) {
                $query->where(['Apartments.energy_class' => $energyClass]);
            }
            
            // Recherche par adresse
            $address = $this->request->getQuery('address');
            if ($address) {
                $query->where(['Apartments.address LIKE' => '%' . $address . '%']);
            }

            $apartments = $this->paginate($query);

            $response = [
                'success' => true,
                'message' => 'List of apartments',
                'data' => $apartments,
                'code' => 200,
            ];

            Cache::write($cacheKey, $response, 'api');
        }

        // Add pagination info to metadata
        $paging = $this->request->getAttribute('paging')['Apartments'] ?? [];
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
     * View method
     *
     * @param string|null $id Apartment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): ?Response
    {
        $cacheKey = 'apartment_' . $id;
        $cachedData = Cache::read($cacheKey, 'api');

        if ($cachedData !== null) {
            $response = $cachedData;
            $formattedResponse = $this->formatResponse($response);
            $this->set($formattedResponse);
            $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));
            return null;
        }

        try {
            $apartment = $this->Apartments->get($id, contain: []);

            $response = [
                'success' => true,
                'message' => 'Apartment details',
                'data' => $apartment,
                'code' => 200,
            ];

            // Store in cache
            Cache::write($cacheKey, $response, 'api');
        } catch (RecordNotFoundException $e) {
            $this->response = $this->response->withStatus(404);
            $response = [
                'success' => false,
                'message' => 'Apartment not found',
                'code' => 404,
            ];
        }

        $formattedResponse = $this->formatResponse($response);
        $this->set($formattedResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));

        return null;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add(): ?Response
    {
        $apartment = $this->Apartments->newEmptyEntity();
        $statusCode = 200;

        if ($this->request->is('post')) {
            try {
                $apartment = $this->Apartments->patchEntity($apartment, $this->request->getData());

                if ($this->Apartments->save($apartment)) {
                    $statusCode = 201;
                    $response = [
                        'success' => true,
                        'message' => 'The apartment has been saved.',
                        'data' => $apartment,
                        'code' => $statusCode,
                    ];

                    // Clear cache
                    Cache::clear('api');
                } else {
                    $statusCode = 400;
                    $response = [
                        'success' => false,
                        'message' => 'The apartment could not be saved. Please, try again.',
                        'errors' => $apartment->getErrors(),
                        'code' => $statusCode,
                    ];
                }
            } catch (\PDOException $e) {
                $statusCode = 500;
                $response = [
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage(),
                    'code' => $statusCode,
                ];
            } catch (\Exception $e) {
                $statusCode = 500;
                $response = [
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage(),
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
     * Edit method
     *
     * @param string|null $id Apartment id.
     * @return \Cake\Http\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $statusCode = 405;
        $response = ['success' => false, 'message' => 'Invalid method', 'code' => $statusCode];

        try {
            $apartment = $this->Apartments->get($id, contain: []);

            if ($this->request->is(['patch'])) {
                try {
                    $apartment = $this->Apartments->patchEntity($apartment, $this->request->getData());
                    if ($this->Apartments->save($apartment)) {
                        $statusCode = 200;
                        $response = [
                            'success' => true,
                            'message' => 'The apartment has been saved.',
                            'data' => $apartment,
                            'code' => $statusCode,
                        ];

                        // Clear cache
                        Cache::delete('apartment_' . $id, 'api');
                        Cache::clear('api');
                    } else {
                        $statusCode = 400;
                        $response = [
                            'success' => false,
                            'message' => 'The apartment could not be saved. Please, try again.',
                            'errors' => $apartment->getErrors(),
                            'code' => $statusCode,
                        ];
                    }
                } catch (\PDOException $e) {
                    $statusCode = 500;
                    $response = [
                        'success' => false,
                        'message' => 'Database error: ' . $e->getMessage(),
                        'code' => $statusCode,
                    ];
                } catch (\Exception $e) {
                    $statusCode = 500;
                    $response = [
                        'success' => false,
                        'message' => 'An error occurred: ' . $e->getMessage(),
                        'code' => $statusCode,
                    ];
                }
            }
        } catch (RecordNotFoundException $e) {
            $statusCode = 404;
            $response = [
                'success' => false,
                'message' => 'Apartment not found',
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
     * Delete method
     *
     * @param string|null $id Apartment id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['delete']); 
        $statusCode = 200;

        try {
            $apartment = $this->Apartments->get($id);
            if ($this->Apartments->delete($apartment)) {
                // Clear cache
                Cache::delete('apartment_' . $id, 'api');
                Cache::clear('api');
            }
        } catch (RecordNotFoundException $e) {
            // Idempotent delete: resource already gone, treat as success
        }

        // Always return success for idempotent delete
        $response = [
            'success' => true,
            'message' => 'The apartment has been deleted successfully.',
            'code' => $statusCode,
        ];

        $this->response = $this->response->withStatus($statusCode);
        $formattedResponse = $this->formatResponse($response);
        $this->set($formattedResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));

        return null;
    }
}
