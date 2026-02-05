<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Http\ServerRequest;
use Authentication\AuthenticationService;
use Firebase\JWT\JWT;

class DebugJwtAuthCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $logFile = LOGS . 'debug_auth_error.log';
        try {
            if (!file_exists(CONFIG . 'jwt.key')) {
                 $msg = "Key file not found";
                 file_put_contents($logFile, $msg);
                 return static::CODE_ERROR;
            }

            $privateKey = file_get_contents(CONFIG . 'jwt.key');
            $payload = [
                'iss' => 'gestion_immo',
                'sub' => 9, 
                'iat' => time(),
                'exp' => time() + 3600,
            ];
            $token = JWT::encode($payload, $privateKey, 'RS256');
            
            $io->out("Debug Token Generated");

            $request = new ServerRequest([
                'environment' => [
                    'REQUEST_URI' => '/api/v1/apartments',
                    'REQUEST_METHOD' => 'GET'
                ]
            ]);
            $request = $request->withHeader('Authorization', 'Bearer ' . $token);

            // Manual Service Construction
            $service = new AuthenticationService();
            $service->loadIdentifier('Authentication.JwtSubject');
             // Load User resolver too just in case JwtSubject falls back or needs it? 
             // JwtSubject purely checks claims. 
             // Wait, usually one wants to map to an ORM entity. 
             // Application.php loads 'Authentication.Password' which uses ORM.
             // If JwtSubject is used, it returns the payload as identity? 
             // Application.php says 'returnPayload' => false.
             // So JwtAuthenticator needs an Identifier that finds the user.
             // JwtSubject identifier just returns data from token.
             
             // If returnPayload is false, JwtAuthenticator expects the Identifier to return an object?
             // Actually, if 'returnPayload' is false, it means "Don't just return the payload, verify it against an identifier".
             
            $service->loadAuthenticator('Authentication.Jwt', [
                'secretKey' => file_get_contents(CONFIG . 'jwt.pem'),
                'algorithm' => 'RS256',
                'returnPayload' => false,
                'header' => 'Authorization',
                'queryParam' => 'token',
            ]);

            // Authenticate
            $service->authenticate($request);
            $result = $service->getResult();

            if ($result->isValid()) {
                $io->success("Authentication SUCCESS!");
                $id = $result->getData();
                if (method_exists($id, 'getOriginalData')) $id = $id->getOriginalData();
                $io->out("Identity: " . json_encode($id));
                return static::CODE_SUCCESS;
            } else {
                $io->error("Authentication FAILED");
                
                $reasons = $result->getErrors();
                $output = "Status: " . $result->getStatus() . "\n";
                if (empty($reasons)) {
                     $output .= "No failure reasons returned.\n";
                } else {
                    $output .= "Failure Reasons:\n" . print_r($reasons, true);
                }
                file_put_contents($logFile, $output);
                $io->out("Check log file: $logFile");
                
                return static::CODE_ERROR;
            }
        } catch (\Throwable $t) {
            $msg = "Exception: " . $t->getMessage() . "\n" . $t->getTraceAsString();
            file_put_contents($logFile, $msg);
            $io->error("Exception caught. Check $logFile");
            return static::CODE_ERROR;
        }
    }
}
