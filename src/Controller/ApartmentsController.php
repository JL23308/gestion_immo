<?php
declare(strict_types=1);

namespace App\Controller;

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

    public function index()
    {
        $query = $this->Apartments->find();
        $apartments = $this->paginate($query);

        $response = [
            'message' => 'List of apartments',
            'data' => $apartments,
        ];

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', ['response']);
    }

    /**
     * View method
     *
     * @param string|null $id Apartment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        try {
            $apartment = $this->Apartments->get($id, contain: []);
            
            $response = [
                'message' => 'Apartment details',
                'data' => $apartment,
            ];
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->response = $this->response->withStatus(404);
            $response = [
                'message' => 'Apartment not found',
            ];
        }

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', ['response']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $apartment = $this->Apartments->newEmptyEntity();
        $statusCode = 200;

        if ($this->request->is('post')) {
            $apartment = $this->Apartments->patchEntity($apartment, $this->request->getData());

            if ($this->Apartments->save($apartment)) {
                $statusCode = 201;
                $response = [
                    'message' => 'The apartment has been saved.',
                    'data' => $apartment,
                ];
            } else {
                $statusCode = 400;
                $response = [
                    'message' => 'The apartment could not be saved. Please, try again.',
                    'errors' => $apartment->getErrors(),
                ];
            }
        } else {
             $statusCode = 405;
             $response = [
                'message' => 'Invalid request method. POST required.',
            ];
        }

        $this->response = $this->response->withStatus($statusCode);
        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', ['response']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Apartment id.
     * @return \Cake\Http\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $response = ['message' => 'Invalid method'];
        $statusCode = 405;

        try {
            $apartment = $this->Apartments->get($id, contain: []);
            
            if ($this->request->is(['patch', 'post', 'put'])) {
                $apartment = $this->Apartments->patchEntity($apartment, $this->request->getData());
                if ($this->Apartments->save($apartment)) {
                    $statusCode = 200;
                    $response = [
                        'message' => 'The apartment has been saved.',
                        'data' => $apartment
                    ];
                } else {
                    $statusCode = 400;
                    $response = [
                        'message' => 'The apartment could not be saved. Please, try again.',
                        'errors' => $apartment->getErrors()
                    ];
                }
            }
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $statusCode = 404;
            $response = [
                'message' => 'Apartment not found',
            ];
        }

        $this->response = $this->response->withStatus($statusCode);
        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', ['response']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Apartment id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $statusCode = 203;
        $response = ['message' => 'The apartment has been deleted.'];

        try {
            $apartment = $this->Apartments->get($id);
            if (!$this->Apartments->delete($apartment)) {
                $statusCode = 400;
                $response = ['message' => 'The apartment could not be deleted. Please, try again.'];
            }
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $statusCode = 203;
            $response = [
                'message' => 'The apartment has been deleted.',
            ];
        }

        $this->response = $this->response->withStatus($statusCode);
        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', ['response']);
    }
}
