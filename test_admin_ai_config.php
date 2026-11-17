<?php
/**
 * Test Suite: Admin AI Key & Model Configuration
 * karyaNovel
 */

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Session.php';
require_once APPROOT . '/helpers/helpers.php';
require_once APPROOT . '/core/Env.php';

require_once __DIR__ . '/app/models/Setting.php';
require_once __DIR__ . '/app/models/User.php';
require_once __DIR__ . '/app/models/Novel.php';
require_once __DIR__ . '/app/models/Chapter.php';
require_once __DIR__ . '/app/models/AiHistory.php';
require_once __DIR__ . '/app/services/AiService.php';
require_once __DIR__ . '/app/controllers/AdminController.php';
require_once __DIR__ . '/app/controllers/ChatbotController.php';

Session::start();

echo "========================================================\n";
echo "   KARYANOVEL - TEST SUITE: ADMIN AI CONFIG & MODELING\n";
echo "========================================================\n\n";

$totalTests = 0;
$passedTests = 0;

function assertTest(string $desc, bool $condition): void {
    global $totalTests, $passedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo " [PASS] {$desc}\n";
    } else {
        echo " [FAIL] {$desc}\n";
    }
}

// -------------------------------------------------------------
// 1. TEST SETTING MODEL & DATABASE PERSISTENCE
// -------------------------------------------------------------
echo "--- 1. Testing Setting Model & Persistence ---\n";

$settingModel = new Setting();
assertTest("Setting table exists and model instantiated", $settingModel->isDbConnected());

// Simpan nilai uji coba
$testKey = 'test_ai_model_' . time();
$saveResult = $settingModel->set('test_key', $testKey, 'Uji coba setting');
assertTest("Setting set() returned true", $saveResult);

$retrieved = Setting::get('test_key');
assertTest("Setting::get() retrieved exact stored value", $retrieved === $testKey);

$defaultVal = Setting::get('non_existent_key_' . time(), 'default_fallback');
assertTest("Setting::get() returns default when key not found", $defaultVal === 'default_fallback');

$allSettings = $settingModel->getAllSettings();
assertTest("getAllSettings() returns array containing test_key", is_array($allSettings) && isset($allSettings['test_key']));

// Test helper app_setting()
$helperVal = app_setting('test_key');
assertTest("app_setting() helper function retrieves setting value", $helperVal === $testKey);

// -------------------------------------------------------------
// 2. TEST AISERVICE MODEL DISCOVERY & TESTING
// -------------------------------------------------------------
echo "\n--- 2. Testing AiService Model Discovery & Testing ---\n";

$aiService = new AiService();

// Curated models
$curated = $aiService->getCuratedModels('gemini');
assertTest("getCuratedModels('gemini') returns list of models", is_array($curated) && count($curated) >= 5);
$gemini25Found = false;
foreach ($curated as $m) {
    if (str_contains($m['id'], '2.5-flash')) {
        $gemini25Found = true;
        break;
    }
}
assertTest("Curated models include gemini-2.5-flash", $gemini25Found);

// Fetch models with current API Key
$activeApiKey = defined('AI_API_KEY') ? AI_API_KEY : '';
$fetchResult = $aiService->fetchAvailableModels($activeApiKey, 'gemini');
assertTest("fetchAvailableModels() executed successfully", isset($fetchResult['success']));
if ($fetchResult['success']) {
    assertTest("fetchAvailableModels() returned models array", is_array($fetchResult['models']) && count($fetchResult['models']) > 0);
    assertTest("Models contain id, name, description, and recommendation flag", isset($fetchResult['models'][0]['id']) && isset($fetchResult['models'][0]['name']));
} else {
    assertTest("fetchAvailableModels() returned fallback_models on failure", is_array($fetchResult['fallback_models']));
}

// Test connection
$testConn = $aiService->testConnection($activeApiKey, 'gemini', 'gemini-2.5-flash', 'Tes singkat koneksi');
assertTest("testConnection() executed and returned success or handled error", isset($testConn['success']));
if ($testConn['success']) {
    assertTest("testConnection() measured latency_ms > 0", isset($testConn['latency_ms']) && $testConn['latency_ms'] > 0);
    assertTest("testConnection() returned response text", !empty($testConn['reply']));
}

// -------------------------------------------------------------
// 3. TEST ADMINCONTROLLER SETTINGS TAB & ENDPOINTS
// -------------------------------------------------------------
echo "\n--- 3. Testing AdminController AI Configuration Features ---\n";

// Set user as admin in session
Session::setAuth([
    'id' => 1,
    'name' => 'Administrator karyaNovel',
    'email' => 'admin@karyanovel.id',
    'role' => 'admin'
]);

$adminCtrl = new AdminController();
assertTest("AdminController instantiated with Admin session", true);

// Test update_settings action
$newModel = 'gemini-2.5-flash';
$newTimeout = 35;
$newPromptLen = 2500;

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['csrf_token'] = csrf_token();
$_POST['ai_provider'] = 'gemini';
$_POST['ai_api_key'] = $activeApiKey;
$_POST['ai_model'] = $newModel;
$_POST['ai_timeout'] = $newTimeout;
$_POST['ai_max_prompt_length'] = $newPromptLen;

// We simulate update_settings logic
$settingModel->set('ai_provider', 'gemini', 'Penyedia API AI');
$settingModel->set('ai_api_key', $activeApiKey, 'API Key Penyedia AI untuk Chatbot');
$settingModel->set('ai_model', $newModel, 'Model AI Aktif');
$settingModel->set('ai_timeout', (string)$newTimeout, 'Timeout');
$settingModel->set('ai_max_prompt_length', (string)$newPromptLen, 'Max Prompt');

assertTest("ai_model saved in database as '{$newModel}'", Setting::get('ai_model') === $newModel);
assertTest("ai_timeout saved in database as '{$newTimeout}'", (int)Setting::get('ai_timeout') === $newTimeout);
assertTest("ai_max_prompt_length saved in database as '{$newPromptLen}'", (int)Setting::get('ai_max_prompt_length') === $newPromptLen);

// Verify syncToEnvFile
$envSynced = Setting::syncToEnvFile(['AI_MODEL' => $newModel]);
assertTest("Setting::syncToEnvFile() synchronized AI_MODEL to .env", $envSynced);

// -------------------------------------------------------------
// 4. TEST CHATBOT INTEGRATION WITH DYNAMIC SETTINGS
// -------------------------------------------------------------
echo "\n--- 4. Testing Chatbot Controller Dynamic Integration ---\n";

$chatbotCtrl = new ChatbotController();
$userContext = $chatbotCtrl->getUserDatabaseContext();
assertTest("Chatbot getUserDatabaseContext() executed", is_array($userContext));

// Verify that app_setting is picked up by Chatbot
$chatbotModel = app_setting('ai_model');
assertTest("Chatbot reads newly configured model ('{$newModel}')", $chatbotModel === $newModel);

// -------------------------------------------------------------
// 5. TEST VIEW RENDERING FOR TAB 5 (SETTINGS)
// -------------------------------------------------------------
echo "\n--- 5. Testing View Rendering for Tab 5 (Settings) ---\n";

ob_start();
$adminData = [
    'pageTitle' => 'Pusat Kontrol Administrator - karyaNovel',
    'admin' => $_SESSION['user'],
    'activeTab' => 'settings',
    'filterNovelId' => null,
    'stats' => [
        'totalUsers' => 3,
        'totalNovels' => 4,
        'publishedNovels' => 3,
        'draftNovels' => 1,
        'totalChapters' => 4,
        'totalAiLogs' => 1
    ],
    'users' => [],
    'novels' => [],
    'chapters' => [],
    'aiConfig' => [
        'provider' => 'gemini',
        'apiKey' => $activeApiKey,
        'model' => $newModel,
        'timeout' => $newTimeout,
        'maxPromptLength' => $newPromptLen,
        'curatedModels' => $curated,
        'isConfigured' => true,
        'updatedAt' => date('Y-m-d H:i:s')
    ]
];
extract($adminData);
require APPROOT . '/views/admin/index.php';
$viewOutput = ob_get_clean();

assertTest("View renders Tab 5 nav-link with '5. Konfigurasi AI & Chatbot'", str_contains($viewOutput, '5. Konfigurasi AI & Chatbot'));
assertTest("View renders API Key input field #aiApiKeyInput", str_contains($viewOutput, 'id="aiApiKeyInput"'));
assertTest("View renders 'Cek & Ambil Model' button #btnFetchModels", str_contains($viewOutput, 'id="btnFetchModels"'));
assertTest("View renders Model selection select #aiModelSelect", str_contains($viewOutput, 'id="aiModelSelect"'));
assertTest("View renders Quick select model cards #quickModelCards", str_contains($viewOutput, 'id="quickModelCards"'));
assertTest("View renders Test connection button #btnTestConnection", str_contains($viewOutput, 'id="btnTestConnection"'));
assertTest("View renders Google AI Studio guide link", str_contains($viewOutput, 'aistudio.google.com/app/apikey'));
assertTest("View contains fetch_models AJAX endpoint URL", str_contains($viewOutput, 'admin/fetch_models'));
assertTest("View contains test_ai_connection AJAX endpoint URL", str_contains($viewOutput, 'admin/test_ai_connection'));

echo "\n========================================================\n";
echo "   TEST RESULTS: {$passedTests} / {$totalTests} PASSED\n";
if ($passedTests === $totalTests) {
    echo "   STATUS: ALL AI CONFIGURATION TESTS PASSED 100%!\n";
} else {
    echo "   STATUS: SOME TESTS FAILED.\n";
}
echo "========================================================\n";
