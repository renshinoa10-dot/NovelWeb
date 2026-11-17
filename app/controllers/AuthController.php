<?php
/**
 * karyaNovel - AuthController
 * Mengatur alur otentikasi lengkap (Multi-Aktor: Pembaca, Penulis, Admin),
 * Registrasi dengan Verifikasi OTP WhatsApp via Fonnte, Login Fleksibel (Email/WA), dan Logout
 */

if (!class_exists('FonnteService')) {
    require_once APPROOT . '/services/FonnteService.php';
}

class AuthController extends Controller {
    private User $userModel;
    private FonnteService $fonnteService;

    // Kunci otorisasi khusus untuk mendaftar sebagai Admin
    private const ADMIN_PASSCODE = 'KARYANOVEL2026';

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->fonnteService = new FonnteService();
    }

    /**
     * Halaman & Proses Login
     * Mendukung login dengan Alamat Email atau Nomor WhatsApp
     * Mendukung Multi-Aktor: Pembaca, Penulis, dan Admin
     */
    public function login(): void {
        $this->requireGuest();

        $errors = [];
        $oldIdentifier = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) return;

            // Terima identifier dari input (bisa berupa email atau nomor telepon WhatsApp)
            $identifier = trim(sanitize($_POST['identifier'] ?? ($_POST['email'] ?? '')));
            $password = $_POST['password'] ?? '';
            $oldIdentifier = $identifier;

            // Validasi input
            if (empty($identifier)) {
                $errors['identifier'] = 'Alamat email atau nomor WhatsApp wajib diisi.';
            }

            if (empty($password)) {
                $errors['password'] = 'Kata sandi wajib diisi.';
            }

            if (empty($errors)) {
                $user = $this->userModel->authenticate($identifier, $password);

                if ($user) {
                    // Cek apakah akun sudah diverifikasi melalui OTP WhatsApp
                    if (isset($user['is_verified']) && (int)$user['is_verified'] === 0) {
                        // Akun belum diverifikasi: Generate OTP baru dan arahkan ke verifikasi
                        $otpCode = sprintf('%06d', random_int(100000, 999999));
                        $this->userModel->setOtp((int)$user['id'], $otpCode, 10);

                        // Kirim OTP via Fonnte ke nomor WhatsApp pengguna
                        $phone = $user['phone'] ?? '';
                        if (!empty($phone)) {
                            $this->fonnteService->sendOtpVerification($phone, $otpCode, $user['name']);
                        }

                        // Simpan sesi pending verifikasi
                        Session::set('pending_otp_user_id', (int)$user['id']);
                        Session::set('pending_otp_phone', $phone);
                        Session::set('pending_otp_name', $user['name']);
                        Session::set('pending_otp_role', $user['role']);

                        set_flash("Akun Anda belum terverifikasi. Kami telah mengirimkan kode OTP baru ke WhatsApp {$phone}.", 'warning');
                        redirect('auth/verify_otp');
                        return;
                    }

                    // User telah terverifikasi: Set sesi login
                    Session::setAuth($user);

                    // Redirect sesuai Aktor
                    $role = $user['role'] ?? 'pembaca';
                    if ($role === 'admin') {
                        set_flash("Selamat datang kembali, Administrator {$user['name']}!", 'success');
                        redirect('admin');
                    } elseif ($role === 'penulis') {
                        set_flash("Selamat datang kembali, Penulis {$user['name']}!", 'success');
                        redirect('dashboard');
                    } else {
                        set_flash("Selamat datang kembali di karyaNovel, {$user['name']}!", 'success');
                        redirect('novels?public=1');
                    }
                    return;
                } else {
                    $errors['general'] = 'Kombinasi akun (email/WhatsApp) atau kata sandi tidak cocok.';
                    set_flash('Kombinasi akun (email/WhatsApp) atau kata sandi tidak cocok.', 'danger');
                }
            }
        }

        $data = [
            'pageTitle' => 'Masuk ke karyaNovel',
            'metaDescription' => 'Masuk ke akun karyaNovel Anda (Pembaca, Penulis, Admin) dengan Email atau WhatsApp.',
            'errors' => $errors,
            'oldIdentifier' => $oldIdentifier
        ];

        $this->view('auth/login', $data, 'main');
    }

    /**
     * Halaman & Proses Registrasi Pengguna Baru
     * Mendukung pemilihan Aktor: Pembaca, Penulis, dan Admin
     * Wajib Verifikasi Kode OTP Acak via Service Fonnte WhatsApp
     */
    public function register(): void {
        $this->requireGuest();

        $errors = [];
        $oldInput = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'role' => 'pembaca'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) return;

            $name = trim(sanitize($_POST['name'] ?? ''));
            $email = trim(sanitize($_POST['email'] ?? ''));
            $phone = trim(sanitize($_POST['phone'] ?? ''));
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $role = trim(sanitize($_POST['role'] ?? 'pembaca'));
            $adminPasscode = trim($_POST['admin_passcode'] ?? '');

            $oldInput['name'] = $name;
            $oldInput['email'] = $email;
            $oldInput['phone'] = $phone;
            $oldInput['role'] = $role;

            // 1. Validasi Nama
            if (empty($name)) {
                $errors['name'] = 'Nama lengkap / nama pena wajib diisi.';
            } elseif (mb_strlen($name) < 3) {
                $errors['name'] = 'Nama minimal 3 karakter.';
            }

            // 2. Validasi Email
            if (empty($email)) {
                $errors['email'] = 'Alamat email wajib diisi.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Format alamat email tidak valid.';
            } elseif ($this->userModel->isEmailTaken($email)) {
                $errors['email'] = 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk.';
            }

            // 3. Validasi Nomor Telepon (WhatsApp untuk OTP Fonnte)
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (empty($phone)) {
                $errors['phone'] = 'Nomor WhatsApp wajib diisi untuk pengiriman kode verifikasi OTP.';
            } elseif (strlen($cleanPhone) < 9 || strlen($cleanPhone) > 16) {
                $errors['phone'] = 'Nomor WhatsApp tidak valid (minimal 9 digit).';
            } elseif ($this->userModel->isPhoneTaken($cleanPhone)) {
                $errors['phone'] = 'Nomor WhatsApp ini sudah terdaftar. Gunakan nomor lain atau login.';
            }

            // Standarisasi nomor telepon untuk Fonnte (format: 08xxx atau 628xxx)
            if (str_starts_with($cleanPhone, '0')) {
                $normalizedPhone = '0' . substr($cleanPhone, 1);
            } elseif (str_starts_with($cleanPhone, '62')) {
                $normalizedPhone = $cleanPhone;
            } else {
                $normalizedPhone = '0' . $cleanPhone;
            }

            // 4. Validasi Role Aktor
            $allowedRoles = ['pembaca', 'penulis', 'admin'];
            if (!in_array($role, $allowedRoles, true)) {
                $role = 'pembaca';
            }

            // Jika memilih role admin, wajibkan passcode keamanan admin
            if ($role === 'admin') {
                if (empty($adminPasscode)) {
                    $errors['admin_passcode'] = 'Passcode Keamanan Administrator wajib diisi untuk role Admin.';
                } elseif ($adminPasscode !== self::ADMIN_PASSCODE) {
                    $errors['admin_passcode'] = 'Passcode Keamanan Administrator salah. Silakan hubungi tim IT.';
                }
            }

            // 5. Validasi Kata Sandi
            if (empty($password)) {
                $errors['password'] = 'Kata sandi wajib diisi.';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Kata sandi minimal 6 karakter.';
            }

            // 6. Validasi Konfirmasi Sandi
            if (empty($confirmPassword)) {
                $errors['confirm_password'] = 'Konfirmasi kata sandi wajib diisi.';
            } elseif ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Konfirmasi kata sandi tidak cocok dengan kata sandi.';
            }

            // Jika tidak ada error validasi, buat akun dan kirim OTP via Fonnte
            if (empty($errors)) {
                // Generate 6 digit angka OTP acak
                $otpCode = sprintf('%06d', random_int(100000, 999999));
                $otpExpiry = date('Y-m-d H:i:s', time() + (10 * 60)); // Berlaku 10 menit

                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $normalizedPhone,
                    'password' => $password,
                    'role' => $role,
                    'otp_code' => $otpCode,
                    'otp_expiry' => $otpExpiry,
                    'is_verified' => 0 // Belum diverifikasi sampai memasukkan OTP
                ];

                $userId = $this->userModel->register($userData);

                if ($userId) {
                    // Kirim kode OTP via Fonnte Service WhatsApp Gateway
                    $fonnteRes = $this->fonnteService->sendOtpVerification($normalizedPhone, $otpCode, $name);

                    // Simpan status verifikasi di Sesi
                    Session::set('pending_otp_user_id', (int)$userId);
                    Session::set('pending_otp_phone', $normalizedPhone);
                    Session::set('pending_otp_name', $name);
                    Session::set('pending_otp_role', $role);

                    if (!$fonnteRes['success']) {
                        // Fonnte API mengalami kendala jaringan atau nomor tidak terdaftar WA
                        set_flash("Akun berhasil dibuat. Namun pengiriman WhatsApp terkendala: {$fonnteRes['message']}. Silakan cek nomor atau kirim ulang OTP.", 'warning');
                    } else {
                        set_flash("Kode OTP verifikasi 6 digit telah dikirimkan ke WhatsApp Anda ({$normalizedPhone}).", 'success');
                    }

                    redirect('auth/verify_otp');
                    return;
                } else {
                    $errors['general'] = 'Terjadi kesalahan pada sistem saat registrasi. Silakan coba lagi.';
                    set_flash('Terjadi kesalahan pada sistem saat registrasi. Silakan coba lagi.', 'danger');
                }
            }
        }

        $data = [
            'pageTitle' => 'Daftar Akun Baru - karyaNovel',
            'metaDescription' => 'Daftar akun karyaNovel sebagai Pembaca, Penulis, atau Admin dengan verifikasi nomor WhatsApp OTP resmi.',
            'errors' => $errors,
            'oldInput' => $oldInput
        ];

        $this->view('auth/register', $data, 'main');
    }

    /**
     * Halaman & Proses Verifikasi Kode OTP WhatsApp
     */
    public function verify_otp(): void {
        $pendingUserId = Session::get('pending_otp_user_id');
        $phone = Session::get('pending_otp_phone');
        $name = Session::get('pending_otp_name', 'Pengguna');
        $role = Session::get('pending_otp_role', 'pembaca');

        // Jika tidak ada data sesi pending dan user belum login
        if (!$pendingUserId && !Session::isLoggedIn()) {
            set_flash('Sesi verifikasi tidak ditemukan. Silakan masuk atau daftar akun terlebih dahulu.', 'warning');
            redirect('auth/login');
            return;
        }

        $userId = $pendingUserId ?: (int)Session::userId();
        $user = $this->userModel->find($userId);

        if (!$user) {
            set_flash('Data akun tidak ditemukan.', 'danger');
            redirect('auth/login');
            return;
        }

        // Jika akun sudah terverifikasi sebelumnya
        if ((int)($user['is_verified'] ?? 0) === 1) {
            Session::remove('pending_otp_user_id');
            Session::setAuth($user);
            set_flash("Akun Anda sudah aktif dan terverifikasi.", 'info');
            $this->redirectByRole($user['role']);
            return;
        }

        $phone = $user['phone'] ?? $phone;
        $name = $user['name'] ?? $name;
        $errors = [];

        // Penanganan input kode OTP
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) return;

            $otpInput = trim(sanitize($_POST['otp'] ?? ''));

            if (empty($otpInput)) {
                $errors['otp'] = 'Kode OTP verifikasi 6 digit wajib diisi.';
            } elseif (strlen($otpInput) !== 6 || !ctype_digit($otpInput)) {
                $errors['otp'] = 'Kode OTP harus berupa 6 digit angka.';
            } else {
                $verifyResult = $this->userModel->verifyOtp($userId, $otpInput);

                if ($verifyResult['success']) {
                    // Verifikasi Berhasil! Ambil data user terkini
                    $verifiedUser = $this->userModel->find($userId);

                    // Bersihkan sesi pending
                    Session::remove('pending_otp_user_id');
                    Session::remove('pending_otp_phone');
                    Session::remove('pending_otp_name');
                    Session::remove('pending_otp_role');

                    // Set Autentikasi Pengguna
                    Session::setAuth($verifiedUser);

                    $roleTitle = match ($verifiedUser['role']) {
                        'admin' => 'Administrator',
                        'penulis' => 'Penulis',
                        default => 'Pembaca'
                    };

                    set_flash("Selamat datang di karyaNovel, {$verifiedUser['name']}! Akun {$roleTitle} Anda telah berhasil diverifikasi dan aktif.", 'success');
                    $this->redirectByRole($verifiedUser['role']);
                    return;
                } else {
                    $errors['otp'] = $verifyResult['message'] ?? 'Kode OTP salah atau telah kedaluwarsa.';
                    set_flash($errors['otp'], 'danger');
                }
            }
        }

        // Masking nomor telepon untuk privasi tampilan: 0896****0208
        $maskedPhone = $phone;
        if (strlen($phone) >= 8) {
            $prefix = substr($phone, 0, 4);
            $suffix = substr($phone, -4);
            $maskedPhone = $prefix . '••••' . $suffix;
        }

        $data = [
            'pageTitle' => 'Verifikasi OTP WhatsApp - karyaNovel',
            'metaDescription' => 'Masukkan 6 digit kode OTP yang telah dikirimkan ke WhatsApp Anda.',
            'userId' => $userId,
            'phone' => $phone,
            'maskedPhone' => $maskedPhone,
            'name' => $name,
            'role' => $role,
            'errors' => $errors
        ];

        $this->view('auth/verify_otp', $data, 'main');
    }

    /**
     * Kirim Ulang Kode OTP via Fonnte Service
     */
    public function resend_otp(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/verify_otp');
            return;
        }

        if (!$this->validateCsrf()) return;

        $userId = Session::get('pending_otp_user_id') ?: (int)Session::userId();
        if (!$userId) {
            set_flash('Sesi verifikasi telah kedaluwarsa. Silakan masuk kembali.', 'warning');
            redirect('auth/login');
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            set_flash('Pengguna tidak ditemukan.', 'danger');
            redirect('auth/login');
            return;
        }

        $phone = $user['phone'] ?? '';
        if (empty($phone)) {
            set_flash('Nomor WhatsApp tidak terdaftar pada akun ini.', 'danger');
            redirect('auth/verify_otp');
            return;
        }

        // Generate kode OTP baru
        $newOtp = sprintf('%06d', random_int(100000, 999999));
        $this->userModel->setOtp($userId, $newOtp, 10);

        // Kirim via Fonnte
        $fonnteRes = $this->fonnteService->sendOtpVerification($phone, $newOtp, $user['name']);

        if ($fonnteRes['success']) {
            set_flash("Kode OTP baru berhasil dikirimkan ke nomor WhatsApp {$phone}.", 'success');
        } else {
            set_flash("Gagal mengirimkan kode OTP via WhatsApp: {$fonnteRes['message']}. Silakan coba lagi.", 'warning');
        }

        redirect('auth/verify_otp');
    }

    /**
     * Logout Pengguna
     */
    public function logout(): void {
        Session::destroy();
        Session::start();
        set_flash('Anda telah berhasil keluar dari sistem karyaNovel.', 'info');
        redirect('auth/login');
    }

    /**
     * Helper: Pengalihan rute berdasarkan Role Aktor
     */
    private function redirectByRole(string $role): void {
        if ($role === 'admin') {
            redirect('admin');
        } elseif ($role === 'penulis') {
            redirect('dashboard');
        } else {
            redirect('novels?public=1');
        }
    }
}
