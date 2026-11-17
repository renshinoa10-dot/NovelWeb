/**
 * karyaNovel - Core Client-side Scripts & UI/UX Enhancements
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Navbar Scroll Effect
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 20) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 2. Initialize Bootstrap Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    // 3. Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // 4. Smooth scrolling for hash links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#' && targetId.length > 1) {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // 5. Reading Progress Bar (Active on Reading Pages)
    const progressBar = document.getElementById('readingProgressBar');
    if (progressBar) {
        window.addEventListener('scroll', function () {
            const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
            progressBar.style.width = Math.min(100, Math.max(0, scrolled)) + '%';
        });
    }

    // 6. Form Button Loading States
    document.querySelectorAll('form').forEach(form => {
        // Exclude AJAX forms, Chatbot widget forms, or forms marked with .no-loading-form
        if (
            form.id === 'knChatbotForm' || 
            form.classList.contains('no-loading-form') || 
            form.classList.contains('ajax-form') ||
            form.closest('#karyanovel-chatbot-widget') ||
            form.closest('#knChatbotWrapper')
        ) {
            return;
        }

        form.addEventListener('submit', function (e) {
            // Check if form is valid before triggering loading state
            if (this.checkValidity && !this.checkValidity()) {
                return;
            }
            const submitBtn = this.querySelector('button[type="submit"]:not(.no-loading)');
            if (submitBtn && !submitBtn.disabled) {
                const originalHtml = submitBtn.innerHTML;
                submitBtn.dataset.originalHtml = originalHtml;
                // Add spinner
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...';
                submitBtn.classList.add('btn-loading');
            }
        });
    });

    // 7. Initialize Reader Preferences from LocalStorage
    initReaderPreferences();
});

// =========================================================================
// Global Toast Notification Helper
// =========================================================================
window.showKnToast = function (message, icon = 'bi-check-circle-fill text-success') {
    let toast = document.getElementById('knToastGlobal');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'knToastGlobal';
        toast.className = 'kn-toast';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<i class="bi ${icon} fs-5"></i> <span>${message}</span>`;
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3500);
};

// =========================================================================
// Reading Experience Helper (Font, Theme, Size)
// =========================================================================
function initReaderPreferences() {
    const readerCard = document.querySelector('.chapter-content-card');
    const chapterBody = document.querySelector('.chapter-body');
    if (!readerCard || !chapterBody) return;

    // Load saved preferences
    const savedTheme = localStorage.getItem('karyanovel_reader_theme') || 'dark';
    const savedFont = localStorage.getItem('karyanovel_reader_font') || 'sans';
    const savedSize = localStorage.getItem('karyanovel_reader_size') || '1.18';

    setReaderTheme(savedTheme, false);
    setReaderFont(savedFont, false);
    setReaderFontSize(parseFloat(savedSize), false);
}

window.setReaderTheme = function (theme, save = true) {
    const readerCard = document.querySelector('.chapter-content-card');
    if (!readerCard) return;

    readerCard.classList.remove('reader-theme-dark', 'reader-theme-sepia', 'reader-theme-oled');
    readerCard.classList.add('reader-theme-' + theme);

    // Update active state in theme buttons if exist
    document.querySelectorAll('.btn-reader-theme').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.theme === theme);
    });

    if (save) {
        localStorage.setItem('karyanovel_reader_theme', theme);
        showKnToast('Tema bacaan diubah: ' + (theme === 'sepia' ? 'Hangat Sepia' : (theme === 'oled' ? 'OLED Hitam' : 'Mode Gelap')));
    }
};

window.setReaderFont = function (font, save = true) {
    const chapterBody = document.querySelector('.chapter-body');
    if (!chapterBody) return;

    chapterBody.classList.remove('reader-font-serif', 'reader-font-sans');
    chapterBody.classList.add('reader-font-' + font);

    // Update active state in font buttons
    document.querySelectorAll('.btn-reader-font').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.font === font);
    });

    if (save) {
        localStorage.setItem('karyanovel_reader_font', font);
        showKnToast('Gaya huruf: ' + (font === 'serif' ? 'Serif Sastra' : 'Sans Modern'));
    }
};

let currentReaderFontSize = 1.18;
window.changeReaderFontSize = function (delta) {
    const chapterBody = document.querySelector('.chapter-body');
    if (!chapterBody) return;

    currentReaderFontSize = Math.max(0.9, Math.min(1.6, currentReaderFontSize + (delta * 0.1)));
    chapterBody.style.fontSize = currentReaderFontSize.toFixed(2) + 'rem';
    localStorage.setItem('karyanovel_reader_size', currentReaderFontSize.toFixed(2));
};

function setReaderFontSize(sizeRem, save = true) {
    const chapterBody = document.querySelector('.chapter-body');
    if (!chapterBody) return;

    currentReaderFontSize = Math.max(0.9, Math.min(1.6, sizeRem));
    chapterBody.style.fontSize = currentReaderFontSize + 'rem';
    if (save) {
        localStorage.setItem('karyanovel_reader_size', currentReaderFontSize);
    }
}

// =========================================================================
// AI Typing Simulation Demo for Hero Section
// =========================================================================
const aiDemoSentences = [
    '"Ia tahu pertarungan ini bukan lagi tentang membalaskan dendam perguruan, melainkan mencegah gerbang dimensi langit runtuh sebelum fajar menyingsing."',
    '"Hembusan angin malam membawa aroma daun basah dan keputusasaan. Namun, di dalam tatapan matanya, tekad itu tak pernah padam sedikit pun."',
    '"Dari balik kabut tebal, siluet naga giok perlahan muncul, mengaumkan getaran yang membuat tanah di bawah kaki mereka bergoncang hebat."',
    '"Senyuman tipis tersungging di bibirnya. Sebuah rencana rahasia yang ia simpan selama tujuh tahun akhirnya memasuki fase terakhir."'
];

let currentSentenceIndex = 0;
let isTyping = false;

function simulateAiTyping() {
    if (isTyping) return;
    
    const textElement = document.getElementById('aiDemoText');
    const btn = document.getElementById('demoAiBtn');
    if (!textElement || !btn) return;

    isTyping = true;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menulis...';

    currentSentenceIndex = (currentSentenceIndex + 1) % aiDemoSentences.length;
    const targetText = aiDemoSentences[currentSentenceIndex];
    
    textElement.textContent = '';
    let charIndex = 0;

    const interval = setInterval(function () {
        if (charIndex < targetText.length) {
            textElement.textContent += targetText.charAt(charIndex);
            charIndex++;
        } else {
            clearInterval(interval);
            isTyping = false;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Regenerasi';
        }
    }, 25);
}

// =========================================================================
// Toggle Password Visibility Helper
// =========================================================================
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
}
