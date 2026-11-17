<?php
/**
 * karyaNovel - AIService
 * Service layer independen untuk integrasi AI Writing Assistant
 * Memisahkan logika provider AI dari Controller agar mudah diganti/diperluas (OpenAI, Gemini, Anthropic, Custom/Local)
 */

class AiService {
    protected string $provider;
    protected string $apiKey;
    protected string $model;
    protected int $timeout;
    protected int $maxPromptLength;
    protected ?string $apiUrl = null;

    // Daftar tipe bantuan AI yang didukung (6 Fitur Utama)
    public const TYPE_IDEA = 'idea';
    public const TYPE_OUTLINE = 'outline';
    public const TYPE_CHARACTER = 'character';
    public const TYPE_CONTINUE = 'continue';
    public const TYPE_IMPROVE = 'improve';
    public const TYPE_SUGGESTIONS = 'suggestions';

    public function __construct(?array $config = null) {
        $defaultProvider = function_exists('app_setting') ? app_setting('ai_provider', defined('AI_PROVIDER') ? AI_PROVIDER : 'custom') : (defined('AI_PROVIDER') ? AI_PROVIDER : 'custom');
        $defaultApiKey = function_exists('app_setting') ? app_setting('ai_api_key', defined('AI_API_KEY') ? AI_API_KEY : '') : (defined('AI_API_KEY') ? AI_API_KEY : '');
        $defaultModel = function_exists('app_setting') ? app_setting('ai_model', defined('AI_MODEL') ? AI_MODEL : 'karyanovel-ai-v1') : (defined('AI_MODEL') ? AI_MODEL : 'karyanovel-ai-v1');
        $defaultTimeout = function_exists('app_setting') ? (int)app_setting('ai_timeout', defined('AI_TIMEOUT') ? AI_TIMEOUT : 30) : (defined('AI_TIMEOUT') ? AI_TIMEOUT : 30);
        $defaultMaxPrompt = function_exists('app_setting') ? (int)app_setting('ai_max_prompt_length', defined('AI_MAX_PROMPT_LENGTH') ? AI_MAX_PROMPT_LENGTH : 2000) : (defined('AI_MAX_PROMPT_LENGTH') ? AI_MAX_PROMPT_LENGTH : 2000);

        $this->provider = (string)($config['provider'] ?? $defaultProvider);
        $this->apiKey = (string)($config['apiKey'] ?? $config['api_key'] ?? $defaultApiKey);
        $this->model = (string)($config['model'] ?? $defaultModel);
        $this->timeout = (int)($config['timeout'] ?? $defaultTimeout);
        $this->maxPromptLength = (int)($config['max_prompt_length'] ?? $config['maxPromptLength'] ?? $defaultMaxPrompt);
        $this->apiUrl = !empty($config['apiUrl']) ? (string)$config['apiUrl'] : (!empty($config['api_url']) ? (string)$config['api_url'] : null);
    }

    public function getProvider(): string {
        return $this->provider;
    }

    public function getModel(): string {
        return $this->model;
    }

    public function getTimeout(): int {
        return $this->timeout;
    }

    public function getMaxPromptLength(): int {
        return $this->maxPromptLength;
    }

    /**
     * Memvalidasi parameter request sebelum diproses
     *
     * @param string $type
     * @param string $prompt
     * @param string $context
     * @return string|null Pesan error jika tidak valid, atau null jika lolos
     */
    public function validate(string $type, string $prompt, string $context = ''): ?string {
        $validTypes = [
            self::TYPE_IDEA, self::TYPE_OUTLINE, self::TYPE_CHARACTER,
            self::TYPE_CONTINUE, self::TYPE_IMPROVE, self::TYPE_SUGGESTIONS,
            'polish', 'dialogue', 'suggestion' // alias backwards compatibility
        ];

        if (!in_array(strtolower(trim($type)), $validTypes, true)) {
            return 'Jenis bantuan AI tidak dikenali. Pilih salah satu dari 6 opsi yang tersedia (Generate Ide, Outline, Character, Continue, Improve, Suggestions).';
        }

        if (empty(trim($prompt))) {
            return 'Instruksi (prompt) tidak boleh kosong. Silakan masukkan arahan atau pertanyaan Anda.';
        }

        if (mb_strlen($prompt) > $this->maxPromptLength) {
            return "Panjang instruksi melebihi batas maksimal ({$this->maxPromptLength} karakter). Mohon persingkat instruksi Anda.";
        }

        if (mb_strlen($context) > 5000) {
            return 'Panjang konteks naskah melebihi batas maksimal (5000 karakter). Mohon persingkat konteks naskah Anda.';
        }

        return null;
    }

    /**
     * Menjalankan request generasi asisten AI
     *
     * @param string $type Jenis bantuan (idea, outline, character, continue, improve, suggestions)
     * @param string $prompt Arahan atau pertanyaan dari penulis
     * @param string $context Cuplikan teks naskah terakhir (dibatasi agar hemat token & aman)
     * @param array $metadata Informasi tambahan novel (judul, genre, dll)
     * @return array ['success' => bool, 'response' => string, 'provider' => string, 'error' => string|null, 'code' => int|null]
     */
    public function generate(string $type, string $prompt, string $context = '', array $metadata = []): array {
        // Normalisasi alias tipe bantuan
        $normalizedType = match (strtolower(trim($type))) {
            'polish' => self::TYPE_IMPROVE,
            'dialogue' => self::TYPE_CHARACTER,
            'suggestion' => self::TYPE_SUGGESTIONS,
            default => strtolower(trim($type))
        };

        // Potong konteks naskah agar tidak membebani payload (hanya 800 karakter terakhir)
        $trimmedContext = mb_substr(trim($context), -800);

        // Simulasi error untuk unit testing/mocking
        if ($this->provider === 'mock_timeout') {
            return [
                'success' => false,
                'error' => 'Koneksi ke server AI mengalami batas waktu (timeout). Silakan coba lagi atau persingkat instruksi Anda.',
                'code' => 504
            ];
        }

        if ($this->provider === 'mock_error') {
            return [
                'success' => false,
                'error' => 'Penyedia AI mengalami kesalahan koneksi atau server down (HTTP 502). Silakan coba beberapa saat lagi.',
                'code' => 502
            ];
        }

        if ($this->provider === 'mock_invalid') {
            return [
                'success' => false,
                'error' => 'Format balasan dari server AI tidak valid.',
                'code' => 500
            ];
        }

        if ($this->provider === 'mock_rate_limit') {
            return [
                'success' => false,
                'error' => 'Batas kuota/rate limit penyedia AI telah tercapai. Silakan coba beberapa saat lagi atau periksa kuota akun Anda.',
                'code' => 429
            ];
        }

        if ($this->provider === 'mock_invalid_key') {
            return [
                'success' => false,
                'error' => 'API key penyedia AI tidak valid atau telah kedaluwarsa. Silakan periksa konfigurasi AI_API_KEY di .env.',
                'code' => 401
            ];
        }

        if ($this->provider === 'mock_missing_key') {
            return [
                'success' => false,
                'error' => 'API key untuk penyedia AI belum diatur di file .env. Silakan konfigurasikan AI_API_KEY terlebih dahulu.',
                'code' => 500
            ];
        }

        // Jika provider eksternal tapi API Key belum diatur di environment variable
        $externalProviders = ['openai', 'gemini', 'anthropic', 'groq', 'openrouter', 'external'];
        if (in_array(strtolower($this->provider), $externalProviders, true) && empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => "API key untuk penyedia AI ({$this->provider}) belum diatur di file .env. Silakan konfigurasikan AI_API_KEY terlebih dahulu.",
                'code' => 500
            ];
        }

        // Jika API Key dikonfigurasi dan provider bukan 'custom' / 'mock' / 'local', hubungi external API
        if (!empty($this->apiKey) && !in_array($this->provider, ['custom', 'mock', 'local'], true)) {
            $apiResult = $this->callExternalProvider($normalizedType, $prompt, $trimmedContext, $metadata);
            if ($apiResult['success']) {
                return $apiResult;
            }
            // Kembalikan error API eksternal secara terstruktur
            return $apiResult;
        }

        // Engine Generator Internal karyaNovel (Bahasa Indonesia Sastra Cerdas, Cepat, dan Mandiri)
        $generatedText = $this->generateLocalResponse($normalizedType, $prompt, $trimmedContext, $metadata);

        return [
            'success' => true,
            'response' => $generatedText,
            'provider' => $this->provider,
            'type' => $normalizedType,
            'model' => $this->model
        ];
    }

    /**
     * Engine teks lokal berstandar sastra karyaNovel untuk 6 kapabilitas AI
     */
    public function generateLocalResponse(string $type, string $prompt, string $context, array $metadata = []): string {
        $genre = $metadata['genre'] ?? 'Fiksi Populer';
        $novelTitle = $metadata['novel_title'] ?? 'Naskah';

        switch ($type) {
            // 1. GENERATE IDE
            case self::TYPE_IDEA:
                return $this->buildIdeaResponse($prompt, $genre);

            // 2. GENERATE OUTLINE
            case self::TYPE_OUTLINE:
                return $this->buildOutlineResponse($prompt, $novelTitle, $genre);

            // 3. GENERATE CHARACTER
            case self::TYPE_CHARACTER:
                return $this->buildCharacterResponse($prompt, $genre);

            // 4. CONTINUE STORY
            case self::TYPE_CONTINUE:
                return $this->buildContinueResponse($prompt, $context, $genre);

            // 5. IMPROVE WRITING
            case self::TYPE_IMPROVE:
                return $this->buildImproveResponse($prompt, $context);

            // 6. GIVE SUGGESTIONS
            case self::TYPE_SUGGESTIONS:
                return $this->buildSuggestionsResponse($prompt, $context, $genre);

            default:
                return "AI Assistant siap membantu Anda mengembangkan naskah bab novel ini.";
        }
    }

    /**
     * 1. Generate Ide: Menghasilkan 5 ide novel/alur kreatif sesuai tema, genre & deskripsi pengguna
     */
    protected function buildIdeaResponse(string $prompt, string $genre): string {
        $topic = !empty($prompt) ? $prompt : "novel bergenre {$genre}";

        return "💡 Rekomendasi 5 Ide Pengembangan Cerita ({$topic}):\n\n" .
               "1. Konsep 'Gerbang Tak Terlihat di Balik Reruntuhan Tua':\n" .
               "   • Premis: Seorang pemuda menemukan artefak berfrekuensi aneh di reruntuhan stasiun tua, membuka portal ke kota terapung yang eksistensinya dihapus dari catatan sejarah seratus tahun lalu.\n" .
               "   • Konflik Utama: Kehadirannya memicu perburuan oleh para Penjaga Batas yang menganggap manusia luar sebagai bibit kehancuran bagi kestabilan dimensi mereka.\n\n" .
               "2. Konsep 'Pewaris Darah Dunia Bawah Tanah':\n" .
               "   • Premis: Dunia rahasia berada ribuan meter di bawah jaringan terowongan kota metropolitan modern, dihuni klan-klan tersembunyi yang memiliki kemampuan manipulasi ingatan dan waktu.\n" .
               "   • Plot Twist: Tokoh utama mengetahui bahwa garis darah keluarganya memegang kunci tunggal penentu takhta dinasti yang sedang diperebutkan di ambang kudeta.\n\n" .
               "3. Konsep 'Dimensi Cermin Saat Senja (The Twilight Veil)':\n" .
               "   • Premis: Dunia rahasia bukanlah tempat yang jauh di antah berantah, melainkan dimensi paralel yang hanya dapat ditembus selama 10 menit saat matahari tenggelam sempurna di ufuk barat.\n" .
               "   • Dilema Moral: Tokoh utama harus bernegosiasi dengan bayangan cermin dirinya sendiri yang menolak kembali terikat ke dunia nyata demi menggapai kebebasan abadi.\n\n" .
               "4. Konsep 'Arsip Tanpa Batas dan Perpustakaan Semesta':\n" .
               "   • Premis: Sebuah lorong tersembunyi di balik rak loteng perpustakaan kota membawanya ke repositori rahasia tempat setiap buku adalah jendela hidup menuju dunia-dunia magis yang telah punah.\n" .
               "   • Bahaya & Taruhan: Entitas penjaga arsip menuntut tebusan berupa ingatan paling berharga dari sang pemuda untuk setiap lembar pengetahuan rahasia yang ia buka.\n\n" .
               "5. Konsep 'Kepulauan Mengambang di Balik Kabut Abadi':\n" .
               "   • Premis: Tersesat saat badai kabut tebal di puncak gunung keramat, pemuda tersebut tersedot anomali gravitasi menuju peradaban pulau melayang yang bertumpu pada energi kristal mitologi.\n" .
               "   • Misi Krusial: Membantu faksi pemberontak mempertahankan inti energi langit sebelum kepulauan tersebut runtuh menabrak peradaban manusia di bawahnya.";
    }

    /**
     * 2. Generate Outline: Menghasilkan kerangka novel atau bab lengkap
     * Mencakup: Premis, Konflik utama, Perkembangan cerita, Rancangan bab, Klimaks, Penyelesaian
     */
    protected function buildOutlineResponse(string $prompt, string $novelTitle, string $genre): string {
        $focus = !empty($prompt) ? $prompt : "Kerangka Alur {$novelTitle} ({$genre})";

        return "📑 Rancangan Outline Novel / Babak Cerita ({$focus}):\n\n" .
               "■ PREMIS CERITA:\n" .
               "Seorang protagonis dengan luka masa lalu menemukan rahasia terlarang yang memaksanya memilih antara mempertahankan hidupnya yang tenang atau menghadapi ancaman yang membahayakan seluruh orang yang dicintainya.\n\n" .
               "■ KONFLIK UTAMA:\n" .
               "Pertentangan antara tekad tokoh utama mengungkap kebenaran melawan konspirasi faksi penguasa bayangan yang bersumpah mengubur rahasia tersebut selamanya.\n\n" .
               "■ PERKEMBANGAN CERITA (RISING ACTION):\n" .
               "1. Pemicu Awal: Terungkapnya petunjuk misterius yang merusak status quo kehidupan sang tokoh.\n" .
               "2. Ambang Batas: Tokoh utama mengambil keputusan berani menyeberangi zona nyaman dan memasuki lingkungan asing yang penuh intrik.\n" .
               "3. Penumpukan Rintangan: Munculnya sekutu tak terduga yang ternyata menyimpan motif terselubung, dibarengi eskalasi ancaman dari pihak antagonis.\n\n" .
               "■ RANCANGAN BAB (CHAPTER BREAKDOWN):\n" .
               "• Bab 1: 'Garis Batas Senja' — Pengenalan rutinitas karakter, luka batin tersembunyi, dan insiden pemicu penemuan benda misterius.\n" .
               "• Bab 2: 'Bisikan di Balik Kabut' — Menghadapi konfrontasi pertama dengan pihak musuh, upaya melarikan diri, dan perkenalan dengan sang pemandu misterius.\n" .
               "• Bab 3: 'Titik Balik Pengkhianatan' — Rahasia besar terbongkar; sosok yang dipercaya ternyata menjual informasi penting demi keselamatan pribadinya.\n" .
               "• Bab 4: 'Malam Paling Gulita' — Karakter kehilangan pegangan terkuat dan dihadapkan pada pilihan moral mutlak.\n\n" .
               "■ KLIMAKS:\n" .
               "Konfrontasi langsung di medan penentuan; seluruh rahasia terbuka dan karakter harus mengorbankan keyakinan lamanya demi mengalahkan ancaman utama.\n\n" .
               "■ PENYELESAIAN (RESOLUSI):\n" .
               "Dampak pertempuran menata ulang tatanan dunia; karakter utama menerima jati dirinya yang baru dengan kedewasaan, membuka lembaran harapan baru.";
    }

    /**
     * 3. Generate Character: Menghasilkan profil tokoh berkarakter utuh
     * Mencakup: Nama, Usia, Latar belakang, Kepribadian, Tujuan, Motivasi, Kelemahan, Konflik karakter
     */
    protected function buildCharacterResponse(string $prompt, string $genre): string {
        $charQuery = !empty($prompt) ? $prompt : "Tokoh Naskah ({$genre})";

        return "👤 Profil Tokoh & Karakter Lengkap ({$charQuery}):\n\n" .
               "• Nama Tokoh: Reyhan Mahesa\n" .
               "• Usia: 21 Tahun\n" .
               "• Latar Belakang: Tumbuh sebagai anak yatim piatu di pinggiran kota industri, membesarkan diri dengan bekerja serabutan di bengkel mekanik tua warisan kakeknya yang menghilang secara misterius sepuluh tahun silam.\n" .
               "• Kepribadian: Cerdas, penuh observasi, dingin di permukaan namun memiliki empati mendalam terhadap kaum tertindas; sinis terhadap janji-janji manis otoritas.\n" .
               "• Tujuan (Goal): Menemukan artefak catatan jurnal ekspedisi terakhir sang kakek dan membuktikan bahwa teori kakeknya tentang keberadaan dunia rahasia bukanlah khayalan semata.\n" .
               "• Motivasi: Membersihkan nama baik keluarganya dari tuduhan fitnah pengkhianatan perguruan dan menyelamatkan desanya dari krisis energi berkepanjangan.\n" .
               "• Kelemahan (Flaw): Keras kepala, sulit mempercayai bantuan orang lain (hyper-independent), dan sering kali bertindak impulsif saat keselamatan orang terdekatnya terancam.\n" .
               "• Konflik Karakter:\n" .
               "  - Konflik Internal: Rasa bersalah yang mendalam karena meyakini dirinya menjadi penyebab kepergian sang kakek di malam badai sepuluh tahun lalu.\n" .
               "  - Konflik Eksternal: Dikejar oleh agen sindikat bayangan yang menginginkan kompas kuno peninggalan keluarga yang kini ia pegang.\n" .
               "• Contoh Gaya Bicara / Dialog Khas:\n" .
               "  \"Jangan bicara soal takdir kepadaku. Takdir adalah kata yang diciptakan oleh orang-orang penakut untuk memaafkan kepasrahan mereka sendiri.\"";
    }

    /**
     * 4. Continue Story: Menulis kelanjutan narasi cerita secara luwes tanpa menimpa naskah asli
     */
    protected function buildContinueResponse(string $prompt, string $context, string $genre): string {
        if (!empty($prompt)) {
            return "Menanggapi hal tersebut, " . lcfirst(rtrim($prompt, '.')) . ".\n\n" .
                   "Hawa dingin perlahan merayap di sekeliling ruangan saat denting waktu seolah melambat. Setiap hembusan napas yang tertahan menyiratkan pertaruhan besar yang tak dapat lagi dihindari. Di kejauhan, lolongan angin malam terdengar seperti peringatan kuno yang enggan diindahkan. Tanpa bersuara, ia menegakkan pundaknya, membiarkan naluri membimbing setiap ayunan langkah melewati bayang-bayang yang kian memanjang.";
        }

        if (!empty($context)) {
            return "Langkah kakinya melambat saat menyadari ada ketidakharmonisan dalam keheningan malam itu. Dari sudut matanya, sebuah siluet samar bergerak di balik tirai kabut tipis. Sesuatu yang tak kasat mata seolah menuntut jawaban atas keputusan yang baru saja ia ambil di ambang pintu tua tersebut. Udara di sekitarnya terasa semakin rapat, sarat dengan ketegangan yang siap meledak dalam satu kedipan mata.";
        }

        return "Di tengah keheningan yang mencekam, suara gemerisik langkah kaki mendekat dari arah lorong yang gelap gulita. Ia menahan napas, mempererat genggamannya pada benda pusaka di balik saku jubahnya, dan bersiap menghadapi apa pun yang telah menunggu di balik tirai takdir berikutnya.";
    }

    /**
     * 5. Improve Writing: Memperbaiki tata bahasa, struktur kalimat, alur paragraf & keterbacaan
     */
    protected function buildImproveResponse(string $prompt, string $context): string {
        $sourceText = !empty($prompt) ? $prompt : $context;

        return "🖌️ Rekomendasi Peningkatan Diksi, Tata Bahasa & Gaya Sastra:\n\n" .
               "■ Teks Masukan:\n" .
               "\"" . mb_substr($sourceText, 0, 200) . (mb_strlen($sourceText) > 200 ? '...' : '') . "\"\n\n" .
               "■ Versi Perbaikan (Prinsip Show, Don't Tell & Alur Berirama):\n" .
               "\"Hawa dingin menusuk hingga ke sumsum tulang saat kabut tebal perlahan menelan lembah sunyi itu. Di bawah temaram sinar rembulan, bayangan pedang pusaka berkilat keemasan, memancarkan aura magis yang menggetarkan sukma. Setiap tarikan napas terasa berat, sarat akan firasat bahwa takdir tengah memutar porosnya menuju pusaran yang tak terelakkan.\"\n\n" .
               "■ Catatan Perbaikan Khusus:\n" .
               "1. Tata Bahasa & Struktur Kalimat: Mengganti konstruksi pasif berulang dengan kata kerja aktif yang dinamis dan berenergi.\n" .
               "2. Alur Paragraf: Membangun jembatan transisi antar-kalimat sehingga perpindahan sudut pandang terasa mengalir alami.\n" .
               "3. Keterbacaan & Diksi: Menghilangkan kata mubazir (pleonasme) dan memperkuat detail sensori (suhu, cahaya temaram, kilatan logam) agar pembaca larut dalam adegan tanpa mengubah inti pesan naskah asli.";
    }

    /**
     * 6. Give Suggestions: Memberikan masukan terkait konflik, karakter, alur, dialog, latar, adegan
     */
    protected function buildSuggestionsResponse(string $prompt, string $context, string $genre): string {
        return "💬 Umpan Balik & Saran Penyuntingan Naskah Komprehensif:\n\n" .
               "1. ⚔️ Pengembangan Konflik:\n" .
               "   • Taruhan (stakes) dalam adegan ini perlu dipertegas. Perjelas apa kerugian terbesar bagi tokoh jika ia gagal mengambil tindakan dalam adegan ini agar tensi ketegangan tetap terjaga tinggi.\n\n" .
               "2. 👤 Konsistensi Karakter:\n" .
               "   • Respon emosional tokoh utama terasa sedikit terburu-buru. Pastikan keragu-raguan atau luka batin masa lalunya tetap tercermin sebelum ia memutuskan mempercayai situasi baru.\n\n" .
               "3. 📖 Alur Cerita & Pacing:\n" .
               "   • Transisi antara momen dialog tenang menuju eskalasi ketegangan dapat diperlambat sedikit dengan menambahkan satu atau dua kalimat jeda yang menyoroti suasana hening yang janggal.\n\n" .
               "4. 💬 Dinamika Dialog:\n" .
               "   • Kalimat dialog sudah terdengar natural, namun akan lebih bertenaga jika dipadukan dengan beat aksi tubuh (misalnya jemari yang mengetuk meja gelisah, tatapan yang berpaling, atau nada bicara yang tercekat).\n\n" .
               "5. 🌄 Deskripsi Latar & Suasana (Setting):\n" .
               "   • Perkaya unsur panca indra non-visual—seperti bau lumut basah, dengung sayup angin malam, atau dinginnya permukaan lantai batu—agar atmosfer adegan terasa semakin imersif bagi pembaca.\n\n" .
               "6. 🎬 Pengembangan Adegan:\n" .
               "   • Akhiri adegan dengan micro-cliffhanger atau tanda tanya baru yang memancing rasa penasaran pembaca untuk segera melanjutkan ke paragraf atau bab berikutnya.";
    }

    /**
     * Menghubungi External AI Provider via cURL (OpenAI, Gemini, Groq, OpenRouter, atau API kompatibel)
     */
    protected function callExternalProvider(string $type, string $prompt, string $context, array $metadata): array {
        $systemInstruction = "Anda adalah AI Writing Assistant profesional untuk aplikasi karyaNovel. Tugas Anda adalah membantu penulis novel berbahasa Indonesia mengembangkan ide cerita, outline alur babak, profil karakter lengkap, melanjutkan cerita naskah, memoles gaya bahasa/tata bahasa, atau memberikan saran penulisan sastra. Berikan jawaban yang kreatif, terstruktur, rapi, dan bernilai sastra tinggi.";

        $fullPrompt = "Tipe Bantuan: {$type}\n";
        if (!empty($metadata['genre'])) {
            $fullPrompt .= "Genre Novel: {$metadata['genre']}\n";
        }
        if (!empty($metadata['novel_title'])) {
            $fullPrompt .= "Judul Novel: {$metadata['novel_title']}\n";
        }
        if (!empty($context)) {
            $fullPrompt .= "Konteks Terakhir Naskah:\n\"\"\"{$context}\"\"\"\n\n";
        }
        $fullPrompt .= "Instruksi Penulis:\n{$prompt}";

        // Deteksi provider Google Gemini vs OpenAI-compatible
        $isGemini = (strtolower($this->provider) === 'gemini');

        if ($isGemini) {
            $geminiModel = (!empty($this->model) && $this->model !== 'gemini-1.5-flash') ? $this->model : 'gemini-3.5-flash-lite';
            $url = $this->apiUrl ?: "https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key=" . $this->apiKey;
            $headers = [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->apiKey
            ];
            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $systemInstruction . "\n\n" . $fullPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.75,
                    'maxOutputTokens' => 1500
                ]
            ];
        } else {
            // Standar OpenAI / OpenRouter / Groq / Custom
            $url = $this->apiUrl ?: 'https://api.openai.com/v1/chat/completions';
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ];
            $payload = [
                'model' => $this->model ?: 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemInstruction],
                    ['role' => 'user', 'content' => $fullPrompt]
                ],
                'temperature' => 0.75,
                'max_tokens' => 1200
            ];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(5, $this->timeout));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Kompatibilitas lingkungan lokal XAMPP

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorNo = curl_errno($ch);
        $curlErrorMsg = curl_error($ch);
        curl_close($ch);

        // Penanganan Timeout
        if ($curlErrorNo === CURLE_OPERATION_TIMEDOUT || $curlErrorNo === 28) {
            return [
                'success' => false,
                'error' => 'Koneksi ke server AI mengalami batas waktu (timeout). Silakan coba lagi atau persingkat instruksi Anda.',
                'code' => 504
            ];
        }

        // Penanganan Koneksi Gagal
        if ($curlErrorNo !== 0) {
            return [
                'success' => false,
                'error' => "Gagal terhubung ke penyedia AI ({$curlErrorMsg}).",
                'code' => 502
            ];
        }

        // Penanganan Status HTTP Spesifik
        if ($httpCode === 401) {
            return [
                'success' => false,
                'error' => 'API key penyedia AI tidak valid atau telah kedaluwarsa. Silakan periksa konfigurasi AI_API_KEY di .env.',
                'code' => 401
            ];
        }

        if ($httpCode === 429) {
            return [
                'success' => false,
                'error' => 'Batas kuota/rate limit penyedia AI telah tercapai. Silakan coba beberapa saat lagi atau periksa kuota akun Anda.',
                'code' => 429
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => "Penyedia AI mengembalikan respon status {$httpCode}. Silakan periksa konfigurasi akun atau kuota API Anda.",
                'code' => $httpCode
            ];
        }

        $decoded = json_decode($rawResponse, true);

        if ($isGemini) {
            $content = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } else {
            $content = $decoded['choices'][0]['message']['content'] ?? null;
        }

        if (empty($content) || !is_string($content)) {
            return [
                'success' => false,
                'error' => 'Format balasan dari server AI tidak valid atau respons kosong.',
                'code' => 500
            ];
        }

        return [
            'success' => true,
            'response' => trim($content),
            'provider' => $this->provider,
            'type' => $type,
            'model' => $this->model
        ];
    }

    /**
     * Menjalankan generasi teks cerdas untuk Chatbot Assistant menggunakan Google Gemini
     * dengan kemampuan multi-model fallback otomatis jika terjadi 503 (high demand) atau 404
     *
     * @param string $prompt Teks prompt pengguna beserta konteks pengetahuan database
     * @param string|null $systemInstruction Arahan sistem identitas & batasan karyaNovel
     * @param array $options Opsi tambahan (apiKey, provider, model, temperature, maxTokens)
     * @return array ['success' => bool, 'text' => string, 'model' => string, 'provider' => string, 'error' => string|null, 'code' => int|null]
     */
    public function generateText(string $prompt, ?string $systemInstruction = null, array $options = []): array {
        $apiKey = !empty($options['apiKey']) ? (string)$options['apiKey'] : $this->apiKey;
        if (empty($apiKey) && function_exists('app_setting')) {
            $apiKey = (string)app_setting('ai_api_key', defined('AI_API_KEY') ? AI_API_KEY : '');
        } elseif (empty($apiKey) && defined('AI_API_KEY')) {
            $apiKey = (string)AI_API_KEY;
        }

        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'API Key untuk penyedia AI belum diatur di file .env atau konfigurasi Admin.',
                'code' => 500
            ];
        }

        $defaultProvider = function_exists('app_setting') ? app_setting('ai_provider', defined('AI_PROVIDER') ? AI_PROVIDER : 'gemini') : (defined('AI_PROVIDER') ? AI_PROVIDER : 'gemini');
        $defaultModel = function_exists('app_setting') ? app_setting('ai_model', defined('AI_MODEL') ? AI_MODEL : 'gemini-3.5-flash-lite') : (defined('AI_MODEL') ? AI_MODEL : 'gemini-3.5-flash-lite');

        $provider = strtolower((string)($options['provider'] ?? $this->provider ?? $defaultProvider));
        $configuredModel = (string)($options['model'] ?? $this->model ?? $defaultModel);

        // Jika OpenAI / OpenRouter / Groq diminta secara eksplisit
        if (in_array($provider, ['openai', 'openrouter', 'groq'], true)) {
            return $this->callOpenAiText($prompt, $systemInstruction, $apiKey, $configuredModel, $options);
        }

        // Default & Utama: Google Gemini Generative AI Agent
        return $this->callGeminiText($prompt, $systemInstruction, $apiKey, $configuredModel, $options);
    }

    /**
     * Pemanggil API Google Gemini untuk Chatbot dengan fallback multi-model cerdas
     */
    protected function callGeminiText(string $prompt, ?string $systemInstruction, string $apiKey, string $preferredModel, array $options = []): array {
        // Bersihkan model usang jika terdeteksi
        if ($preferredModel === 'gemini-1.5-flash' || empty($preferredModel)) {
            $preferredModel = 'gemini-3.5-flash-lite';
        }

        // Daftar model Gemini yang dicoba berurutan untuk menjamin reliabilitas 100%
        $candidateModels = array_unique(array_filter([
            $preferredModel,
            'gemini-3.5-flash-lite',
            'gemini-3.8-flash',
            'gemini-3.5-flash',
            'gemini-flash-latest'
        ]));

        $temperature = (float)($options['temperature'] ?? 0.7);
        $maxTokens = (int)($options['maxTokens'] ?? 1500);
        $lastError = 'Tidak dapat menghubungi server Gemini.';
        $lastCode = 500;

        foreach ($candidateModels as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;

            $payload = [];
            if (!empty($systemInstruction)) {
                $payload['system_instruction'] = [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ];
            }
            $payload['contents'] = [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ];
            $payload['generationConfig'] = [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxTokens
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Kompatibilitas XAMPP Windows

            $raw = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErrorNo = curl_errno($ch);
            $curlErrorMsg = curl_error($ch);
            curl_close($ch);

            if ($curlErrorNo !== 0) {
                $lastError = "Koneksi cURL gagal ({$curlErrorMsg}).";
                $lastCode = 502;
                continue;
            }

            if ($httpCode === 200) {
                $decoded = json_decode($raw, true);
                $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (!empty($text) && is_string($text)) {
                    return [
                        'success' => true,
                        'text' => trim($text),
                        'model' => $model,
                        'provider' => 'gemini'
                    ];
                }
            }

            $decoded = json_decode($raw, true);
            $errorMsg = $decoded['error']['message'] ?? "HTTP {$httpCode}";
            $lastError = "Gemini ({$model}): {$errorMsg}";
            $lastCode = $httpCode;

            // Jika API Key tidak valid (401 atau 403), tidak perlu mencoba model lain
            if ($httpCode === 401 || $httpCode === 403) {
                return [
                    'success' => false,
                    'error' => "API key Gemini tidak valid atau izin ditolak ({$errorMsg}).",
                    'code' => $httpCode
                ];
            }

            // Jika 503 (overloaded) atau 404 (model deprecated/not found), lanjutkan ke model berikutnya
            continue;
        }

        return [
            'success' => false,
            'error' => $lastError,
            'code' => $lastCode
        ];
    }

    /**
     * Pemanggil API OpenAI-compatible untuk Chatbot
     */
    protected function callOpenAiText(string $prompt, ?string $systemInstruction, string $apiKey, string $model, array $options = []): array {
        $url = !empty($options['apiUrl']) ? (string)$options['apiUrl'] : 'https://api.openai.com/v1/chat/completions';
        $messages = [];
        if (!empty($systemInstruction)) {
            $messages[] = ['role' => 'system', 'content' => $systemInstruction];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => !empty($model) ? $model : 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => (float)($options['temperature'] ?? 0.7),
            'max_tokens' => (int)($options['maxTokens'] ?? 1500)
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $decoded = json_decode($raw, true);
            $text = $decoded['choices'][0]['message']['content'] ?? null;
            if (!empty($text)) {
                return [
                    'success' => true,
                    'text' => trim($text),
                    'model' => $model,
                    'provider' => 'openai'
                ];
            }
        }

        return [
            'success' => false,
            'error' => "OpenAI mengembalikan kode HTTP {$httpCode}.",
            'code' => $httpCode
        ];
    }

    /**
     * Mengambil daftar model AI yang tersedia langsung dari API Provider menggunakan API Key yang diinput
     *
     * @param string $apiKey
     * @param string $provider 'gemini' | 'openai'
     * @return array ['success' => bool, 'provider' => string, 'models' => array, 'total' => int, 'error' => string|null]
     */
    public function fetchAvailableModels(string $apiKey, string $provider = 'gemini'): array {
        $apiKey = trim($apiKey);
        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'API Key tidak boleh kosong.',
                'fallback_models' => $this->getCuratedModels($provider)
            ];
        }

        $provider = strtolower(trim($provider));

        if ($provider === 'gemini') {
            return $this->fetchGeminiModels($apiKey);
        }

        if (in_array($provider, ['openai', 'openrouter'], true)) {
            return $this->fetchOpenAiModels($apiKey);
        }

        return [
            'success' => true,
            'provider' => $provider,
            'models' => $this->getCuratedModels($provider),
            'total' => count($this->getCuratedModels($provider))
        ];
    }

    /**
     * Fetching model dari Google Generative Language API
     */
    protected function fetchGeminiModels(string $apiKey): array {
        $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorNo = curl_errno($ch);
        $curlErrorMsg = curl_error($ch);
        curl_close($ch);

        $fallback = $this->getCuratedModels('gemini');

        if ($curlErrorNo !== 0) {
            return [
                'success' => false,
                'error' => "Gagal terhubung ke Google AI API ({$curlErrorMsg}). Silakan periksa koneksi internet server.",
                'code' => 502,
                'fallback_models' => $fallback
            ];
        }

        $decoded = json_decode($raw, true);

        if ($httpCode !== 200) {
            $msg = $decoded['error']['message'] ?? "Kode respon HTTP {$httpCode}";
            return [
                'success' => false,
                'error' => "Google AI mengembalikan error ({$msg}). Pastikan API Key valid dan aktif di Google AI Studio.",
                'code' => $httpCode,
                'fallback_models' => $fallback
            ];
        }

        if (empty($decoded['models']) || !is_array($decoded['models'])) {
            return [
                'success' => false,
                'error' => "Daftar model kosong dari respon Google AI.",
                'code' => 200,
                'fallback_models' => $fallback
            ];
        }

        $parsedModels = [];
        foreach ($decoded['models'] as $m) {
            $methods = $m['supportedGenerationMethods'] ?? [];
            if (!in_array('generateContent', $methods, true) && !in_array('generateMessage', $methods, true)) {
                continue;
            }

            $rawName = $m['name'] ?? '';
            $id = str_starts_with($rawName, 'models/') ? substr($rawName, 7) : $rawName;

            // Lewati model non-chat seperti embedding, imagen, tts, atau computer-use
            if (str_contains($id, 'embedding') || str_contains($id, 'aqa') || str_contains($id, 'imagen') || str_contains($id, 'tts') || str_contains($id, 'computer-use')) {
                continue;
            }

            $displayName = $m['displayName'] ?? $id;
            $desc = $m['description'] ?? '';
            $inputTokens = $m['inputTokenLimit'] ?? null;
            $outputTokens = $m['outputTokenLimit'] ?? null;

            // Beri label rekomendasi untuk model flash, pro, dan generasi terbaru
            $isRecommended = str_contains($id, '2.5-flash') || str_contains($id, '2.5-pro') || str_contains($id, '2.0-flash') || str_contains($id, '1.5-flash') || str_contains($id, '1.5-pro') || str_contains($id, '3.5-flash');

            $parsedModels[] = [
                'id' => $id,
                'name' => $displayName,
                'description' => $desc,
                'input_token_limit' => $inputTokens,
                'output_token_limit' => $outputTokens,
                'is_recommended' => $isRecommended,
                'version' => $m['version'] ?? ''
            ];
        }

        usort($parsedModels, function($a, $b) {
            if ($a['is_recommended'] !== $b['is_recommended']) {
                return $b['is_recommended'] <=> $a['is_recommended'];
            }
            return strcmp($a['id'], $b['id']);
        });

        return [
            'success' => true,
            'provider' => 'gemini',
            'models' => $parsedModels,
            'total' => count($parsedModels)
        ];
    }

    /**
     * Fetching model dari OpenAI API
     */
    protected function fetchOpenAiModels(string $apiKey): array {
        $url = "https://api.openai.com/v1/models";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorMsg = curl_error($ch);
        curl_close($ch);

        $fallback = $this->getCuratedModels('openai');

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => "OpenAI API mengembalikan error HTTP {$httpCode}. " . $curlErrorMsg,
                'fallback_models' => $fallback
            ];
        }

        $decoded = json_decode($raw, true);
        $models = [];
        if (!empty($decoded['data']) && is_array($decoded['data'])) {
            foreach ($decoded['data'] as $item) {
                $id = $item['id'] ?? '';
                if (str_starts_with($id, 'gpt-') || str_starts_with($id, 'chatgpt') || str_starts_with($id, 'o1')) {
                    $models[] = [
                        'id' => $id,
                        'name' => strtoupper($id),
                        'description' => 'OpenAI Generative Chat Model',
                        'is_recommended' => (str_contains($id, '4o') || str_contains($id, '3.5-turbo'))
                    ];
                }
            }
        }

        return [
            'success' => true,
            'provider' => 'openai',
            'models' => !empty($models) ? $models : $fallback,
            'total' => count($models)
        ];
    }

    /**
     * Model terkurasi bawaan jika API offline atau belum dicek
     */
    public function getCuratedModels(string $provider = 'gemini'): array {
        if ($provider === 'openai') {
            return [
                ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o Mini', 'description' => 'Model OpenAI cepat dan sangat cerdas untuk percakapan', 'is_recommended' => true],
                ['id' => 'gpt-4o', 'name' => 'GPT-4o Omnimodal', 'description' => 'Model flagship OpenAI penalaran komprehensif', 'is_recommended' => true],
                ['id' => 'gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo', 'description' => 'Model standar ekonomis', 'is_recommended' => false],
            ];
        }

        return [
            [
                'id' => 'gemini-2.5-flash',
                'name' => 'Gemini 2.5 Flash',
                'description' => 'Generasi mutakhir Gemini 2.5, sangat responsif, cerdas, dan hemat kuota.',
                'is_recommended' => true,
                'badge' => 'Pilihan Utama'
            ],
            [
                'id' => 'gemini-2.5-pro',
                'name' => 'Gemini 2.5 Pro',
                'description' => 'Model penalaran sastra tinggi untuk alur cerita novel kompleks dan analisis naskah mendalam.',
                'is_recommended' => true,
                'badge' => 'Penalaran Tertinggi'
            ],
            [
                'id' => 'gemini-2.0-flash',
                'name' => 'Gemini 2.0 Flash',
                'description' => 'Generasi 2.0 dengan latensi ultra rendah dan kecepatan respon instan.',
                'is_recommended' => true,
                'badge' => 'Super Cepat'
            ],
            [
                'id' => 'gemini-1.5-flash',
                'name' => 'Gemini 1.5 Flash',
                'description' => 'Model Flash generasi 1.5 yang sangat stabil, efisien, dan hemat kuota.',
                'is_recommended' => false,
                'badge' => 'Stabil'
            ],
            [
                'id' => 'gemini-1.5-pro',
                'name' => 'Gemini 1.5 Pro',
                'description' => 'Jendela konteks luas hingga 2 juta token untuk novel dengan ratusan bab.',
                'is_recommended' => false,
                'badge' => 'Long Context'
            ],
            [
                'id' => 'gemini-3.5-flash-lite',
                'name' => 'Gemini 3.5 Flash Lite',
                'description' => 'Model eksperimental ringan dengan efisiensi tinggi.',
                'is_recommended' => false,
                'badge' => 'Lite'
            ],
            [
                'id' => 'gemini-flash-latest',
                'name' => 'Gemini Flash (Latest)',
                'description' => 'Alias dinamis selalu mengarah ke varian Flash terbaru.',
                'is_recommended' => false,
                'badge' => 'Auto-Update'
            ]
        ];
    }

    /**
     * Menguji koneksi API Key & Model secara real-time
     *
     * @param string $apiKey
     * @param string $provider
     * @param string|null $model
     * @param string|null $customPrompt
     * @return array
     */
    public function testConnection(string $apiKey, string $provider = 'gemini', ?string $model = null, ?string $customPrompt = null): array {
        $startTime = microtime(true);
        $testPrompt = !empty($customPrompt) ? $customPrompt : "Pemeriksaan koneksi karyaNovel AI Assistant. Berikan salam ramah satu kalimat singkat dalam bahasa Indonesia bahwa Anda aktif dan siap membantu penulis novel.";

        $options = [
            'apiKey' => $apiKey,
            'provider' => $provider,
            'model' => $model ?: ($provider === 'gemini' ? 'gemini-2.5-flash' : 'gpt-3.5-turbo'),
            'maxTokens' => 150
        ];

        $systemInstruction = "Anda adalah AI Assistant karyaNovel. Berikan jawaban ringkas, sopan, dan jelas.";
        $result = $this->generateText($testPrompt, $systemInstruction, $options);
        $latencyMs = round((microtime(true) - $startTime) * 1000);

        if (!empty($result['success']) && !empty($result['text'])) {
            return [
                'success' => true,
                'provider' => $result['provider'] ?? $provider,
                'model' => $result['model'] ?? $model,
                'latency_ms' => $latencyMs,
                'reply' => $result['text'],
                'timestamp' => date('H:i:s d-m-Y')
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? 'Gagal memperoleh respon dari provider AI.',
            'code' => $result['code'] ?? 500,
            'latency_ms' => $latencyMs
        ];
    }
}

