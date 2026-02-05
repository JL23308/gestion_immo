<?php
declare(strict_types=1);

namespace App\Error;

use Cake\Error\Renderer\WebExceptionRenderer;

/**
 * Application exception renderer
 *
 * Customizes the error response format to match the API response structure
 * and removes file and line information from responses.
 */
class AppExceptionRenderer extends WebExceptionRenderer
{
    /**
     * Renders the response for the exception.
     *
     * @return \Cake\Http\Response The response to be sent.
     */
    public function render(): \Cake\Http\Response
    {
        $exception = $this->error;
        $code = $this->_getHttpCode($exception);
        $message = $exception->getMessage();

        // Simplify database error messages
        if ($exception instanceof \PDOException) {
            // Extract just the main error message without query details
            $parts = explode("\n", $message);
            $message = $parts[0];
            
            // Remove SQLSTATE prefix if present
            $message = preg_replace('/^SQLSTATE\[\w+\]:\s*/', '', $message);
        }

        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code,
        ];

        // Add URL if available
        if ($this->controller && $this->controller->getRequest()) {
            $response['url'] = $this->controller->getRequest()->getRequestTarget();
        }

        $this->controller->set('response', $response);
        $this->controller->viewBuilder()
            ->setOption('serialize', ['response'])
            ->setClassName('Json');

        return $this->_outputMessage($this->template);
    }

    /**
     * Get the HTTP status code for an exception
     *
     * @param \Throwable $exception The exception
     * @return int The HTTP status code
     */
    protected function _getHttpCode(\Throwable $exception): int
    {
        $code = $exception->getCode();
        
        if ($code < 400 || $code > 599) {
            $code = 500;
        }

        return $code;
    }
}
