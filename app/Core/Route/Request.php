<?php

namespace App\Core\Route;

class Request
{
    protected array $routeParams = [];
    protected array $headers = [];
    protected array $files = [];
    protected array $json = [];
    protected bool $jsonParsed = false;

    public function __construct()
    {
        $this->headers = $this->getAllHeaders();
        $this->files = $this->normalizeFiles($_FILES);
    }

    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return rawurldecode($uri);
    }

    public function method(): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Handle method spoofing for HTML forms
        if ($method === 'POST') {
            $spoofedMethod = $this->input('_method');
            if ($spoofedMethod && in_array(strtoupper($spoofedMethod), ['PUT', 'PATCH', 'DELETE'])) {
                return strtoupper($spoofedMethod);
            }
        }
        
        return $method;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function input(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $_REQUEST[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->routeParams, $_REQUEST);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    public function has(string $key): bool
    {
        return $this->input($key) !== null;
    }

    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return !is_null($value) && $value !== '';
    }

    public function missing(string $key): bool
    {
        return !$this->filled($key);
    }

    public function getHeader(string $key, $default = null)
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function bearerToken(): ?string
    {
        $header = $this->getHeader('authorization');
        if ($header && strpos(strtolower($header), 'bearer ') === 0) {
            return substr($header, 7);
        }
        return null;
    }

    public function ip(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function isJson(): bool
    {
        return strpos($this->getHeader('content-type', ''), 'application/json') !== false;
    }

    public function isAjax(): bool
    {
        return strtolower($this->getHeader('x-requested-with', '')) === 'xmlhttprequest';
    }

    public function isPjax(): bool
    {
        return strtolower($this->getHeader('x-pjax', '')) === 'true';
    }

    public function expectsJson(): bool
    {
        return $this->isJson() || $this->isAjax();
    }

    public function json(string $key = null, $default = null)
    {
        if (!$this->jsonParsed) {
            $this->parseJson();
        }

        if ($key === null) {
            return $this->json;
        }

        return $this->json[$key] ?? $default;
    }

    protected function parseJson(): void
    {
        if ($this->isJson() && $this->method() !== 'GET') {
            $input = file_get_contents('php://input');
            $this->json = json_decode($input, true) ?? [];
        }
        $this->jsonParsed = true;
    }

    public function file(string $key): ?UploadedFile
    {
        return $this->files[$key] ?? null;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]->isValid();
    }

    protected function getAllHeaders(): array
    {
        $headers = [];
        
        if (function_exists('getallheaders')) {
            $serverHeaders = getallheaders();
            foreach ($serverHeaders as $name => $value) {
                $headers[strtolower($name)] = $value;
            }
        } else {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) === 'HTTP_') {
                    $headerName = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($name, 5)))));
                    $headers[strtolower($headerName)] = $value;
                }
            }
        }
        
        return $headers;
    }

    protected function normalizeFiles(array $files): array
    {
        $normalized = [];
        
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $normalized[$key] = [];
                foreach ($file['name'] as $i => $name) {
                    $normalized[$key][$i] = new UploadedFile(
                        $file['tmp_name'][$i],
                        $file['name'][$i],
                        $file['type'][$i],
                        $file['size'][$i],
                        $file['error'][$i]
                    );
                }
            } else {
                $normalized[$key] = new UploadedFile(
                    $file['tmp_name'],
                    $file['name'],
                    $file['type'],
                    $file['size'],
                    $file['error']
                );
            }
        }
        
        return $normalized;
    }

    public function validate(array $rules): array
    {
        $errors = [];
        $data = $this->all();
        
        foreach ($rules as $field => $rule) {
            $ruleList = is_string($rule) ? explode('|', $rule) : $rule;
            
            foreach ($ruleList as $singleRule) {
                if (strpos($singleRule, ':') !== false) {
                    [$ruleName, $parameter] = explode(':', $singleRule, 2);
                } else {
                    $ruleName = $singleRule;
                    $parameter = null;
                }
                
                $error = $this->validateRule($field, $ruleName, $parameter, $data[$field] ?? null);
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }
        
        return $errors;
    }

    protected function validateRule(string $field, string $rule, ?string $parameter, $value): ?string
    {
        switch ($rule) {
            case 'required':
                return is_null($value) || $value === '' ? "The {$field} field is required." : null;
                
            case 'email':
                return !filter_var($value, FILTER_VALIDATE_EMAIL) ? "The {$field} must be a valid email address." : null;
                
            case 'min':
                $min = (int) $parameter;
                if (is_string($value) && strlen($value) < $min) {
                    return "The {$field} must be at least {$min} characters.";
                }
                if (is_numeric($value) && $value < $min) {
                    return "The {$field} must be at least {$min}.";
                }
                return null;
                
            case 'max':
                $max = (int) $parameter;
                if (is_string($value) && strlen($value) > $max) {
                    return "The {$field} may not be greater than {$max} characters.";
                }
                if (is_numeric($value) && $value > $max) {
                    return "The {$field} may not be greater than {$max}.";
                }
                return null;
                
            case 'numeric':
                return !is_numeric($value) ? "The {$field} must be a number." : null;
                
            case 'alpha':
                return !preg_match('/^[a-zA-Z]+$/', $value) ? "The {$field} may only contain letters." : null;
                
            case 'alpha_num':
                return !preg_match('/^[a-zA-Z0-9]+$/', $value) ? "The {$field} may only contain letters and numbers." : null;
                
            case 'url':
                return !filter_var($value, FILTER_VALIDATE_URL) ? "The {$field} must be a valid URL." : null;
                
            default:
                return null;
        }
    }
}
