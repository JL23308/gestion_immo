<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Firebase\JWT\JWT;


/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['login', 'register']);

        return null;
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */



    public function login(): ?Response
    {
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            $privateKey = file_get_contents(CONFIG . 'jwt.key');
            $user = $result->getData();
            $payload = [
                'iss' => 'gestion_immo',
                'sub' => $user->id,
                'iat' => time(),
                'exp' => time() + 60 * 60 * 24, // 24 heures
            ];

            $token = JWT::encode($payload, $privateKey, 'RS256');

            $response = [
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ],
                ],
                'code' => 200,
            ];
            $formattedResponse = $this->formatResponse($response);
            $this->set($formattedResponse);
            $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));
        } else {
            $this->response = $this->response->withStatus(401);
            $response = [
                'success' => false,
                'message' => 'Invalid credentials',
                'code' => 401,
            ];
            $formattedResponse = $this->formatResponse($response);
            $this->set($formattedResponse);
            $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));
        }

        return null;
    }

    /**
     * Register method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */



    public function register(): ?Response
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->response = $this->response->withStatus(201);
                $response = [
                    'success' => true,
                    'message' => 'User created',
                    'data' => $user,
                    'code' => 201,
                ];
                $formattedResponse = $this->formatResponse($response);
                $this->set($formattedResponse);
                $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));
            } else {
                $this->response = $this->response->withStatus(400);
                $response = [
                    'success' => false,
                    'message' => 'Registration failed',
                    'errors' => $user->getErrors(),
                    'code' => 400,
                ];
                $formattedResponse = $this->formatResponse($response);
                $this->set($formattedResponse);
                $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));
            }
        }

        return null;
    }


}
