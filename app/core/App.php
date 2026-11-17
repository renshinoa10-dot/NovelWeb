<?php
/**
 * karyaNovel - Core Application Router
 * Mengatur parsing URL dan pemanggilan Controller, Action/Method, serta Parameter
 */

class App {
    protected object|string $controller = DEFAULT_CONTROLLER;
    protected string $method = DEFAULT_METHOD;
    protected array $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Cek Controller
        if (!empty($url[0])) {
            $rawController = ucfirst($url[0]);
            $controllerName = $rawController . 'Controller';
            $controllerFile = APPROOT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controllerName . '.php';

            // Jika tidak ditemukan secara langsung, periksa bentuk singular/plural (e.g. Novels <-> Novel)
            if (!file_exists($controllerFile)) {
                if (str_ends_with($rawController, 's')) {
                    $altName = substr($rawController, 0, -1) . 'Controller';
                    $altFile = APPROOT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $altName . '.php';
                    if (file_exists($altFile)) {
                        $controllerName = $altName;
                        $controllerFile = $altFile;
                    }
                } else {
                    $altName = $rawController . 'sController';
                    $altFile = APPROOT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $altName . '.php';
                    if (file_exists($altFile)) {
                        $controllerName = $altName;
                        $controllerFile = $altFile;
                    }
                }
            }

            if (file_exists($controllerFile)) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                $this->renderNotFound("Halaman tidak ditemukan.");
                return;
            }
        }

        // Muat file controller
        $controllerPath = APPROOT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $this->controller . '.php';
        if (!file_exists($controllerPath)) {
            $this->renderNotFound("Controller {$this->controller} tidak ditemukan.");
            return;
        }

        require_once $controllerPath;
        $this->controller = new $this->controller;

        // 2. Deteksi HTTP Method & Penanganan Method Override (_method = PUT/PATCH/DELETE)
        $httpMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (isset($_POST['_method'])) {
            $override = strtoupper(trim($_POST['_method']));
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'])) {
                $httpMethod = $override;
            }
        }

        // Cek Method & Parameter (Mendukung pola RESTful: /novels, /novels/create, /novels/1, /novels/1/edit, /novels/edit/1)
        if (isset($url[1])) {
            // Jika segmen ke-2 adalah angka (ID), contoh: /novels/1 atau /novels/1/edit
            if (is_numeric($url[1])) {
                $resourceId = (int)$url[1];
                unset($url[1]);

                if (isset($url[2]) && in_array(strtolower($url[2]), ['edit', 'delete', 'update', 'show', 'detail', 'chapters', 'read', 'publish', 'toggle_status'])) {
                    $actionMethod = strtolower($url[2]);
                    unset($url[2]);
                    if (method_exists($this->controller, $actionMethod)) {
                        $this->method = $actionMethod;
                    }
                } else {
                    // Pola RESTful berdasarkan HTTP Method untuk /resource/{id}
                    if ($httpMethod === 'DELETE') {
                        if (method_exists($this->controller, 'delete')) {
                            $this->method = 'delete';
                        }
                    } elseif (in_array($httpMethod, ['POST', 'PUT', 'PATCH'])) {
                        if (method_exists($this->controller, 'update')) {
                            $this->method = 'update';
                        } elseif (method_exists($this->controller, 'edit')) {
                            $this->method = 'edit';
                        }
                    } else {
                        // Default GET untuk /resource/{id} adalah show() atau detail()
                        if (method_exists($this->controller, 'show')) {
                            $this->method = 'show';
                        } elseif (method_exists($this->controller, 'detail')) {
                            $this->method = 'detail';
                        }
                    }
                }
                $this->params = array_merge([$resourceId], $url ? array_values($url) : []);
            } else {
                $methodName = str_replace('-', '_', $url[1]);
                if (!str_starts_with($methodName, '_') && is_callable([$this->controller, $methodName])) {
                    $reflector = new ReflectionMethod($this->controller, $methodName);
                    if ($reflector->isPublic() && !$reflector->isConstructor() && !$reflector->isDestructor()) {
                        $this->method = $methodName;
                        unset($url[1]);
                        $this->params = $url ? array_values($url) : [];
                    } else {
                        $this->renderNotFound("Aksi/Method tidak ditemukan.");
                        return;
                    }
                } else {
                    $this->renderNotFound("Aksi/Method tidak ditemukan.");
                    return;
                }
            }
        } else {
            // Jika request ke /resource tanpa segmen ke-2 (e.g. POST /novels)
            if ($httpMethod === 'POST') {
                if (method_exists($this->controller, 'store')) {
                    $this->method = 'store';
                } elseif (method_exists($this->controller, 'create')) {
                    $this->method = 'create';
                }
            }
            $this->params = $url ? array_values($url) : [];
        }

        // 3. Jalankan controller & method dengan parameter
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Membaca dan mem-parsing parameter URL
     */
    protected function parseUrl(): array {
        $rawUrl = '';
        if (isset($_GET['url'])) {
            $rawUrl = $_GET['url'];
        } else {
            $rawUrl = $_SERVER['PATH_INFO'] ?? '';
            if (empty($rawUrl) && isset($_SERVER['REQUEST_URI'])) {
                $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
                if ($scriptDir !== '/' && $scriptDir !== '\\' && !empty($scriptDir)) {
                    $rawUrl = substr($requestUri, strlen($scriptDir));
                }
            }
        }

        $rawUrl = trim($rawUrl, '/');
        if (str_starts_with($rawUrl, 'index.php')) {
            $rawUrl = trim(substr($rawUrl, 9), '/');
        }

        if (!empty($rawUrl)) {
            $sanitized = filter_var($rawUrl, FILTER_SANITIZE_URL);
            return explode('/', $sanitized);
        }

        return [];
    }

    /**
     * Menampilkan tampilan error 404
     */
    protected function renderNotFound(string $message = 'Halaman yang Anda cari tidak ditemukan.'): void {
        http_response_code(404);
        $view404 = APPROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . '404.php';
        $layoutFile = APPROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main.php';

        $data = [
            'pageTitle' => '404 - Halaman Tidak Ditemukan',
            'message' => $message
        ];
        extract($data);

        if (file_exists($view404)) {
            ob_start();
            require $view404;
            $content = ob_get_clean();

            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
            echo $content;
        } else {
            echo "<h1>404 Not Found</h1><p>{$message}</p><p><a href='" . base_url() . "'>Kembali ke Beranda</a></p>";
        }
    }
}
