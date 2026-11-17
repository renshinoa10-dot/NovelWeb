/**
 * karyaNovel - Client-side Chatbot Assistant Controller
 * Menangani interaksi UI popup floating, animasi, validasi, integrasi backend, dan persistensi sesi
 */

document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('karyanovel-chatbot-widget') || document.getElementById('knChatbotWrapper');
    if (!wrapper) return;

    const endpoint = wrapper.dataset.endpoint || '/chatbot/message';
    const triggerBtn = document.getElementById('chatbot-toggle') || document.getElementById('knChatbotTriggerBtn');
    const panel = document.getElementById('chatbot-popup') || document.getElementById('knChatbotPanel');
    const minimizeBtn = document.getElementById('knChatbotMinimizeBtn');
    const closeBtn = document.getElementById('knChatbotCloseBtn');
    const form = document.getElementById('knChatbotForm');
    const input = document.getElementById('knChatbotInput');
    const sendBtn = document.getElementById('knChatbotSendBtn');
    const messagesContainer = document.getElementById('knChatbotMessages');
    const typingIndicator = document.getElementById('knTypingIndicator');
    const alertBox = document.getElementById('knChatbotAlert');
    const alertText = document.getElementById('knChatbotAlertText');
    const iconClosed = triggerBtn ? triggerBtn.querySelector('.kn-chatbot-icon-closed') : null;
    const iconOpen = triggerBtn ? triggerBtn.querySelector('.kn-chatbot-icon-open') : null;

    if (!triggerBtn || !panel) return;

    let isOpen = false;
    let isProcessing = false;
    const STORAGE_KEY = 'karyanovel_chatbot_session_history';

    // 1. Inisialisasi status awal: Panel wajib tertutup saat halaman pertama kali dibuka
    panel.style.display = 'none';
    panel.classList.remove('is-open');
    panel.classList.add('d-none');
    panel.setAttribute('aria-hidden', 'true');
    triggerBtn.setAttribute('aria-expanded', 'false');
    if (iconClosed) iconClosed.classList.remove('d-none');
    if (iconOpen) iconOpen.classList.add('d-none');

    // 2. Inisialisasi Tooltip Bootstrap pada Trigger
    let triggerTooltip = null;
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        triggerTooltip = bootstrap.Tooltip.getOrCreateInstance(triggerBtn);
    }

    // 3. Pulihkan Riwayat Percakapan dari SessionStorage
    loadSavedHistory();

    // 4. Event Listener: Toggle Buka / Tutup Panel Floating
    triggerBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (isOpen) {
            closeChatbot();
        } else {
            openChatbot();
        }
    });

    if (minimizeBtn) {
        minimizeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeChatbot();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeChatbot();
        });
    }

    if (panel) {
        panel.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // 5. Keyboard Navigation: Escape untuk menutup popup
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            closeChatbot();
        }
    });

    // 6. Klik di Luar Pop-up untuk Menutup (Click Outside to Close Pop-up)
    document.addEventListener('click', function (e) {
        if (isOpen && panel && !panel.contains(e.target) && !triggerBtn.contains(e.target)) {
            closeChatbot();
        }
    });

    // 7. Event Listener: Tombol Pertanyaan Cepat (Quick Pills)
    attachQuickPills();

    // 8. Event Listener: Submit Form Kirim Pesan
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = input ? input.value.trim() : '';
            if (!text) {
                showAlert('Silakan ketik pertanyaan terlebih dahulu.');
                if (input) input.focus();
                return;
            }
            hideAlert();
            sendMessage(text);
        });
    }

    // 9. Input Realtime: Sembunyikan alert saat pengguna mulai mengetik
    if (input) {
        input.addEventListener('input', function () {
            if (this.value.trim().length > 0) {
                hideAlert();
            }
        });
    }

    // =========================================================================
    // Core Functions
    // =========================================================================

    function openChatbot() {
        isOpen = true;
        panel.style.display = 'flex';
        panel.classList.add('is-open');
        panel.classList.remove('d-none');
        panel.setAttribute('aria-hidden', 'false');
        triggerBtn.setAttribute('aria-expanded', 'true');
        if (iconClosed) iconClosed.classList.add('d-none');
        if (iconOpen) iconOpen.classList.remove('d-none');

        if (triggerTooltip) {
            triggerTooltip.hide();
        }

        scrollToBottom();
        setTimeout(() => {
            if (input) input.focus();
        }, 150);
    }

    function closeChatbot() {
        isOpen = false;
        panel.style.display = 'none';
        panel.classList.remove('is-open');
        panel.classList.add('d-none');
        panel.setAttribute('aria-hidden', 'true');
        triggerBtn.setAttribute('aria-expanded', 'false');
        if (iconClosed) iconClosed.classList.remove('d-none');
        if (iconOpen) iconOpen.classList.add('d-none');
        hideAlert();
    }

    function showAlert(msg) {
        if (!alertBox || !alertText) return;
        alertText.textContent = msg;
        alertBox.classList.remove('d-none');
    }

    function hideAlert() {
        if (!alertBox) return;
        alertBox.classList.add('d-none');
    }

    function scrollToBottom() {
        if (!messagesContainer) return;
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function getCurrentTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Format teks markdown sederhana dan tabel secara aman & estetis
     */
    function formatMessageText(text) {
        if (!text) return '';

        // Pecah per baris untuk memproses blok Markdown (Tabel, Catatan, List, Paragraf)
        const rawLines = text.split('\n');
        let htmlOutput = '';
        let inTable = false;
        let tableHeaderCols = [];
        let tableAlignments = [];
        let tableBodyRows = [];
        let inList = false;
        let listType = 'ul'; // 'ul' atau 'ol'

        function flushTable() {
            if (!inTable) return '';
            let tblHtml = '<div class="kn-chat-table-wrapper"><table class="kn-chat-table"><thead><tr>';
            tableHeaderCols.forEach((col, idx) => {
                const align = tableAlignments[idx] || 'left';
                tblHtml += `<th class="text-${align}">${formatInline(col)}</th>`;
            });
            tblHtml += '</tr></thead><tbody>';

            tableBodyRows.forEach(row => {
                tblHtml += '<tr>';
                row.forEach((col, idx) => {
                    const align = tableAlignments[idx] || 'left';
                    const formattedVal = formatRoleCell(col.trim());
                    tblHtml += `<td class="text-${align}">${formattedVal}</td>`;
                });
                tblHtml += '</tr>';
            });

            tblHtml += '</tbody></table></div>';
            inTable = false;
            tableHeaderCols = [];
            tableAlignments = [];
            tableBodyRows = [];
            return tblHtml;
        }

        function flushList() {
            if (!inList) return '';
            inList = false;
            return `</${listType}>`;
        }

        function formatInline(str) {
            let safe = escapeHtml(str);
            // Bold: **text**
            safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            // Italic: *text*
            safe = safe.replace(/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
            // Inline code: `code`
            safe = safe.replace(/`([^`]+)`/g, '<code class="kn-chat-inline-code">$1</code>');
            // Links: [text](url)
            safe = safe.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer" class="kn-chat-link">$1 <i class="bi bi-box-arrow-up-right small"></i></a>');
            return safe;
        }

        function formatRoleCell(val) {
            const lower = val.toLowerCase();
            if (lower === 'admin' || lower === 'administrator') {
                return `<span class="kn-chat-badge kn-chat-badge-admin"><i class="bi bi-shield-lock-fill me-1"></i>Administrator</span>`;
            }
            if (lower === 'penulis' || lower === 'author') {
                return `<span class="kn-chat-badge kn-chat-badge-penulis"><i class="bi bi-feather me-1"></i>Penulis</span>`;
            }
            if (lower === 'pembaca' || lower === 'reader' || lower === 'user') {
                return `<span class="kn-chat-badge kn-chat-badge-pembaca"><i class="bi bi-book-half me-1"></i>Pembaca</span>`;
            }
            return formatInline(val);
        }

        for (let i = 0; i < rawLines.length; i++) {
            const line = rawLines[i].trim();

            // 1. Deteksi Baris Pemisah Tabel Markdown: |:---|:---|:---| atau |---|---|
            if (line.startsWith('|') && line.endsWith('|') && line.includes('---')) {
                // Baris pemisah tabel, ekstrak alignment
                const alignSegments = line.slice(1, -1).split('|');
                tableAlignments = alignSegments.map(seg => {
                    const s = seg.trim();
                    if (s.startsWith(':') && s.endsWith(':')) return 'center';
                    if (s.endsWith(':')) return 'end';
                    return 'start';
                });
                continue;
            }

            // 2. Deteksi Baris Data / Header Tabel: | No | Nama | Role |
            if (line.startsWith('|') && line.endsWith('|')) {
                const cols = line.slice(1, -1).split('|').map(c => c.trim());
                if (!inTable) {
                    htmlOutput += flushList();
                    inTable = true;
                    tableHeaderCols = cols;
                } else {
                    tableBodyRows.push(cols);
                }
                continue;
            }

            // Jika sebelumnya di dalam tabel tapi baris ini bukan tabel, selesaikan tabel
            if (inTable) {
                htmlOutput += flushTable();
            }

            // 3. Deteksi Catatan / Callout: (Catatan: ...) atau > ...
            const noteMatch = line.match(/^\((?:catatan|note):\s*(.*?)\)\.?$/i) || line.match(/^>\s*(?:catatan:)?\s*(.*?)$/i) || line.match(/^(?:catatan|note):\s*(.*?)$/i);
            if (noteMatch) {
                htmlOutput += flushList();
                const noteContent = noteMatch[1] || noteMatch[2] || '';
                htmlOutput += `<div class="kn-chat-note"><i class="bi bi-info-circle-fill me-2 text-purple-light"></i><span class="kn-chat-note-text">${formatInline(noteContent)}</span></div>`;
                continue;
            }

            // 4. Deteksi List item unordered (- atau *)
            const ulMatch = line.match(/^[-*]\s+(.*)$/);
            if (ulMatch) {
                if (!inList || listType !== 'ul') {
                    htmlOutput += flushList();
                    htmlOutput += '<ul class="kn-chat-list">';
                    inList = true;
                    listType = 'ul';
                }
                htmlOutput += `<li>${formatInline(ulMatch[1])}</li>`;
                continue;
            }

            // 5. Deteksi List item ordered (1. , 2. )
            const olMatch = line.match(/^(\d+)\.\s+(.*)$/);
            if (olMatch) {
                if (!inList || listType !== 'ol') {
                    htmlOutput += flushList();
                    htmlOutput += '<ol class="kn-chat-list">';
                    inList = true;
                    listType = 'ol';
                }
                htmlOutput += `<li>${formatInline(olMatch[2])}</li>`;
                continue;
            }

            // Jika bukan list, flush list jika aktif
            if (inList) {
                htmlOutput += flushList();
            }

            // 6. Baris Kosong
            if (line === '') {
                htmlOutput += '<div class="kn-chat-spacer"></div>';
                continue;
            }

            // 7. Paragraf Teks Biasa
            htmlOutput += `<p class="kn-chat-para mb-1">${formatInline(line)}</p>`;
        }

        // Flush sisa komponen jika ada di akhir
        if (inTable) htmlOutput += flushTable();
        if (inList) htmlOutput += flushList();

        return htmlOutput;
    }

    function appendUserMessage(text, time = null) {
        if (!messagesContainer) return;
        const msgTime = time || getCurrentTime();
        const userRow = document.createElement('div');
        userRow.className = 'kn-message-row user-row';
        userRow.innerHTML = `
            <div class="kn-message-content">
                <div class="kn-message-bubble user-bubble">
                    ${escapeHtml(text)}
                </div>
                <span class="kn-message-time">${msgTime}</span>
            </div>
        `;
        messagesContainer.appendChild(userRow);
        scrollToBottom();
    }

    function appendBotMessage(text, isSimulated = true, time = null) {
        if (!messagesContainer) return;
        const msgTime = time || getCurrentTime();
        const botRow = document.createElement('div');
        botRow.className = 'kn-message-row bot-row';

        const formattedBody = formatMessageText(text);
        const simulationBadge = isSimulated
            ? `<div class="kn-simulated-badge"><i class="bi bi-info-circle"></i> Simulasi Panduan karyaNovel</div>`
            : '';

        botRow.innerHTML = `
            <div class="kn-message-avatar">
                <i class="bi bi-robot"></i>
            </div>
            <div class="kn-message-content">
                <div class="kn-message-bubble bot-bubble">
                    ${formattedBody}
                    ${simulationBadge}
                </div>
                <span class="kn-message-time">${msgTime}</span>
            </div>
        `;
        messagesContainer.appendChild(botRow);
        scrollToBottom();
    }

    function attachQuickPills() {
        const pills = document.querySelectorAll('.kn-quick-pill');
        pills.forEach(pill => {
            pill.addEventListener('click', function () {
                const question = this.dataset.question || this.textContent.trim();
                sendMessage(question);
            });
        });
    }

    // =========================================================================
    // API Request Handling
    // =========================================================================

    function sendMessage(messageText) {
        if (isProcessing) return;

        // 1. Tampilkan pesan pengguna di antarmuka
        appendUserMessage(messageText);
        saveHistoryItem({ sender: 'user', text: messageText, time: getCurrentTime() });

        // Reset input
        if (input) {
            input.value = '';
            input.disabled = true;
        }
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm text-white" role="status" aria-hidden="true" style="width: 1rem; height: 1rem; border-width: 2px;"></span>';
        }
        isProcessing = true;

        // 2. Tampilkan indikator sedang mengetik
        if (typingIndicator) {
            typingIndicator.classList.remove('d-none');
            scrollToBottom();
        }

        // 3. Lakukan request ke backend controller
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                message: messageText
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errData => {
                    throw new Error(errData.message || 'Gagal memproses pesan');
                });
            }
            return response.json();
        })
        .then(res => {
            if (res.success && res.data) {
                const reply = res.data.reply;
                const isSimulated = res.data.is_simulated !== false;
                appendBotMessage(reply, isSimulated, res.data.timestamp);
                saveHistoryItem({
                    sender: 'bot',
                    text: reply,
                    isSimulated: isSimulated,
                    time: res.data.timestamp || getCurrentTime()
                });
            } else {
                appendBotMessage('Maaf, saya tidak dapat memahami pertanyaan tersebut. Silakan coba kembali atau pilih opsi pertanyaan cepat.', true);
            }
        })
        .catch(err => {
            console.error('Chatbot Error:', err);
            appendBotMessage('Maaf, terjadi kendala koneksi ke server asisten. Silakan periksa jaringan dan coba beberapa saat lagi.', true);
        })
        .finally(() => {
            if (typingIndicator) {
                typingIndicator.classList.add('d-none');
            }
            if (input) {
                input.disabled = false;
                input.focus();
            }
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="bi bi-send-fill"></i>';
            }
            isProcessing = false;
            scrollToBottom();
        });
    }

    // =========================================================================
    // Session Storage Persistence
    // =========================================================================

    function saveHistoryItem(item) {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            const history = raw ? JSON.parse(raw) : [];
            history.push(item);
            // Batasi riwayat maksimal 50 pesan terakhir agar memori tetap ringan
            if (history.length > 50) {
                history.shift();
            }
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(history));
        } catch (e) {
            // Ignore storage quota errors
        }
    }

    function loadSavedHistory() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            if (!raw) return;
            const history = JSON.parse(raw);
            if (!Array.isArray(history) || history.length === 0) return;

            history.forEach(item => {
                if (item.sender === 'user') {
                    appendUserMessage(item.text, item.time);
                } else if (item.sender === 'bot') {
                    appendBotMessage(item.text, item.isSimulated, item.time);
                }
            });
        } catch (e) {
            // Abaikan kesalahan deserialisasi
        }
    }
});
