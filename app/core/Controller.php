<?php
/**
 * karyaNovel - Base Controller
 */

abstract class Controller {
    /**
     * Memuat Model
     *
     * @param string $model Nama class model
     * @return object Instance model
     */
    public function model(string $model) {
        $modelFile = APPROOT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }
        throw new Exception("Model '{$model}' tidak ditemukan di {$modelFile}");
    }

    /**
     * Me-render View dengan Layout terpadu
     *
     * @param string $view Path view relatif terhadap app/views/ (contoh: 'home/index')
     * @param array $data Data asosiatif yang dikirim ke view
     * @param string|null $layout Nama layout di app/views/layouts/ (default: 'main', null untuk tanpa layout)
     * @return void
     */
    public function view(string $view, array $data = [], ?string $layout = 'main'): void {
        // Ekstrak data agar dapat diakses langsung sebagai variabel di view
        extract($data);

        $viewFile = APPROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View '{$view}' tidak ditemukan di {$viewFile}");
        }

        // Tangkap isi konten view ke dalam buffer
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render dengan layout jika didefinisikan
        if ($layout) {
            $layoutFile = APPROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }

        // Jika tidak ada layout, cetak konten langsung
        echo $content;
    }

    /**
     * Menampilkan response error 404 Not Found dengan HTTP code yang tepat
     *
     * @param string $message
     * @return void
     */
    protected function notFound(string $message = 'Halaman yang Anda cari tidak ditemukan.'): void {
        if (!headers_sent()) {
            http_response_code(404);
        }
        $this->view('errors/404', [
            'pageTitle' => '404 - Halaman Tidak Ditemukan',
            'message' => $message
        ], 'main');
    }

    /**
     * Mengembalikan response berformat JSON
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    public function json($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Guard: Memeriksa apakah user sudah login
     *
     * @param string $redirect URL redirect jika belum login
     * @param string $message Pesan flash notification
     * @return void
     */
    protected function requireAuth(string $redirect = 'auth/login', string $message = 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.'): void {
        if (!Session::isLoggedIn()) {
            set_flash($message, 'warning');
            redirect($redirect);
        }
    }

    /**
     * Guard: Memeriksa apakah user memiliki role 'admin'
     *
     * @param string $redirect URL redirect jika bukan admin
     * @param string $message Pesan flash notification
     * @return void
     */
    protected function requireAdmin(string $redirect = 'dashboard', string $message = 'Akses ditolak. Anda tidak memiliki izin administrator.'): void {
        $this->requireAuth();
        if (!Session::isAdmin()) {
            set_flash($message, 'danger');
            redirect($redirect);
        }
    }

    /**
     * Guard: Memastikan hanya guest yang bisa mengakses (e.g. login & register page)
     *
     * @return void
     */
    protected function requireGuest(): void {
        if (Session::isLoggedIn()) {
            $user = Session::user();
            $role = $user['role'] ?? 'pembaca';
            if ($role === 'admin') {
                redirect('admin');
            } elseif ($role === 'penulis') {
                redirect('dashboard');
            } else {
                redirect('novels?public=1');
            }
        }
    }

    /**
     * Memeriksa dan memvalidasi keamanan CSRF pada request mutating (POST, PUT, DELETE)
     * Menggabungkan verifikasi Token CSRF, perlindungan Origin header, dan Browser Fetch Metadata (Sec-Fetch-Site)
     *
     * @return bool
     */
    protected function validateCsrf(): bool {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
            // 1. Blokir permintaan Cross-Origin Browser modern (Fetch Metadata)
            if (isset($_SERVER['HTTP_SEC_FETCH_SITE']) && $_SERVER['HTTP_SEC_FETCH_SITE'] === 'cross-site') {
                if ($this->isAjaxOrJson()) {
                    $this->json(['success' => false, 'message' => 'Permintaan lintas domain (Cross-Site Request) ditolak demi keamanan.'], 403);
                } else {
                    set_flash('Permintaan lintas domain ditolak demi keamanan (CSRF Protection).', 'danger');
                    redirect('dashboard');
                }
                return false;
            }

            // 2. Validasi Origin header jika ada
            if (!empty($_SERVER['HTTP_ORIGIN'])) {
                $originHost = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
                $serverHost = !empty($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : 'localhost';
                if ($originHost && $originHost !== $serverHost && $originHost !== 'localhost' && $originHost !== '127.0.0.1') {
                    if ($this->isAjaxOrJson()) {
                        $this->json(['success' => false, 'message' => 'Origin tidak valid atau tidak cocok.'], 403);
                    } else {
                        set_flash('Origin permintaan tidak valid (CSRF Protection).', 'danger');
                        redirect('dashboard');
                    }
                    return false;
                }
            }

            // 3. Verifikasi token CSRF kriptografis
            if (isset($_POST['csrf_token']) || isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
                if (!verify_csrf_token($token)) {
                    if ($this->isAjaxOrJson()) {
                        $this->json(['success' => false, 'message' => 'Token keamanan CSRF tidak valid atau sesi telah berakhir.'], 403);
                    } else {
                        set_flash('Token keamanan CSRF tidak valid atau sesi telah kedaluwarsa. Silakan muat ulang halaman.', 'danger');
                        redirect($_SERVER['HTTP_REFERER'] ?? 'dashboard');
                    }
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Deteksi apakah request berupa AJAX atau meminta balasan JSON
     */
    protected function isAjaxOrJson(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (!empty($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));
    }
}
