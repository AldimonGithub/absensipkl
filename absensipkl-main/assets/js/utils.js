// Utility Functions

// Generate CAPTCHA Code
function generateCaptcha() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let code = '';
    for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return code;
}

// Create CAPTCHA Canvas
function createCaptchaImage(code) {
    const canvas = document.getElementById('captchaCanvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // White background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Add noise lines
    ctx.strokeStyle = '#cccccc';
    for (let i = 0; i < 5; i++) {
        ctx.beginPath();
        ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.stroke();
    }
    
    // Add text
    ctx.fillStyle = '#000000';
    ctx.font = 'bold 28px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    // Add slight rotation to each letter
    let x = canvas.width / 6;
    for (let i = 0; i < code.length; i++) {
        ctx.save();
        ctx.translate(x, canvas.height / 2);
        ctx.rotate((Math.random() - 0.5) * 0.4);
        ctx.fillText(code[i], 0, 0);
        ctx.restore();
        x += canvas.width / 6;
    }
    
    // Add noise dots
    for (let i = 0; i < 50; i++) {
        ctx.fillStyle = '#cccccc';
        ctx.fillRect(
            Math.random() * canvas.width,
            Math.random() * canvas.height,
            2, 2
        );
    }
}

// Get Status Waktu (Morning Attendance Time Status)
function getStatusWaktu(jam_masuk) {
    const time = new Date('2000-01-01 ' + jam_masuk);
    const batas_tepat = new Date('2000-01-01 08:00:00');
    const batas_toleransi = new Date('2000-01-01 08:30:00');
    
    if (time <= batas_tepat) {
        return 'Tepat waktu';
    } else if (time <= batas_toleransi) {
        return 'Toleransi';
    } else {
        return 'Terlambat';
    }
}

// Get Status Pulang (Afternoon Departure Status)
function getStatusPulang(jam_keluar) {
    const time = new Date('2000-01-01 ' + jam_keluar);
    const batas_cepat = new Date('2000-01-01 15:40:00');
    const batas_tepat = new Date('2000-01-01 16:10:00');
    const batas_mulai = new Date('2000-01-01 12:00:00');
    
    if (time < batas_mulai) {
        return null;
    } else if (time <= batas_cepat) {
        return 'Pulang cepat';
    } else if (time <= batas_tepat) {
        return 'Tepat waktu';
    } else {
        return 'Lembur';
    }
}

// Show Alert
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `<span>${message}</span>`;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Format Time
function formatTime(timeString) {
    if (!timeString) return '-';
    const [hour, minute, second] = timeString.split(':');
    return `${hour}:${minute}`;
}

// Format Date
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Get Current DateTime
function getCurrentDateTime() {
    return new Date().toISOString().split('T')[0]; // YYYY-MM-DD
}

// Get Current Time
function getCurrentTime() {
    const now = new Date();
    return now.toTimeString().split(' ')[0]; // HH:MM:SS
}

// Update Clock
function updateClock() {
    const timeDisplay = document.getElementById('timeDisplay');
    if (!timeDisplay) return;
    
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    
    timeDisplay.textContent = `${hours}:${minutes}:${seconds}`;
}

// Start Clock Update
function startClockUpdate() {
    updateClock();
    setInterval(updateClock, 1000);
}

// Check if can attend (6:00 AM or later)
function canAttend() {
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes(); // in minutes
    const minimumTime = 6 * 60; // 06:00 in minutes
    return currentTime >= minimumTime;
}

// Check if can leave (12:00 PM or later)
function canLeave() {
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes(); // in minutes
    const minimumTime = 12 * 60; // 12:00 in minutes
    return currentTime >= minimumTime;
}

// Get Status Color
function getStatusColor(status) {
    const colors = {
        'Hadir': '#28a745',
        'Sakit': '#ffc107',
        'Izin': '#17a2b8',
        'Absen': '#dc3545',
        'Tepat waktu': '#28a745',
        'Toleransi': '#ffc107',
        'Terlambat': '#dc3545',
        'Pulang cepat': '#17a2b8',
        'Lembur': '#6f42c1'
    };
    return colors[status] || '#6c757d';
}

// Apply Status Color to Element
function applyStatusColor(element, status) {
    element.style.color = getStatusColor(status);
}

// Export Data to CSV
function exportToCSV(data, filename) {
    if (!data || data.length === 0) {
        alert('Tidak ada data untuk diexport');
        return;
    }
    
    try {
        const headers = Object.keys(data[0]);
        const csv = [
            headers.join(','),
            ...data.map(row =>
                headers.map(header =>
                    JSON.stringify(row[header] || '')
                ).join(',')
            )
        ].join('\n');
        
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename || 'data.csv');
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
        console.log('CSV exported successfully: ' + filename);
    } catch (error) {
        console.error('Error exporting CSV:', error);
        alert('Gagal export CSV. Error: ' + error.message);
    }
}

function exportToExcel(data, filename) {
    // Redirect to CSV export
    exportToCSV(data, filename);
}

// Validate Phone Number
function validatePhoneNumber(phone) {
    const cleaned = phone.replace(/\D/g, '');
    return cleaned.length >= 10 && cleaned.length <= 13;
}

// Validate Email (if needed later)
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Get Browser Permissions for Camera
async function requestCameraPermission() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        stream.getTracks().forEach(track => track.stop());
        return true;
    } catch (error) {
        console.error('Camera permission denied:', error);
        return false;
    }
}

// Reload Current Page
function reloadPage() {
    location.reload();
}

// Redirect to Page
function redirectTo(url) {
    window.location.href = url;
}

// Get URL Parameters
function getUrlParameter(name) {
    const params = new URLSearchParams(window.location.search);
    return params.get(name);
}

// Logout Function
async function logout() {
    const client = window.absensiSupabase && window.absensiSupabase.client;
    if (client) {
        await client.auth.signOut();
    }
    DB.clearSession();
    redirectTo('index.html');
}

// Check if User is Logged In
function checkLogin() {
    const session = DB.getSession();
    if (!session || !session.logged_in) {
        redirectTo('index.html');
        return false;
    }
    return session;
}

// Check if User is Admin
function checkAdminLogin() {
    const session = DB.getSession();
    if (!session || !session.logged_in || session.user_type !== 'admin') {
        redirectTo('index.html');
        return false;
    }
    return session;
}

// Check if User is Student
function checkStudentLogin() {
    const session = DB.getSession();
    if (!session || !session.logged_in || session.user_type !== 'siswa') {
        redirectTo('index.html');
        return false;
    }
    return session;
}

// Display Current User Info (if element exists)
function displayUserInfo() {
    const session = DB.getSession();
    if (!session) return;
    
    const userNameElements = document.querySelectorAll('.user-name');
    const userTypeElements = document.querySelectorAll('.user-type');
    
    userNameElements.forEach(el => {
        el.textContent = session.user_name;
    });
    
    userTypeElements.forEach(el => {
        el.textContent = session.user_type === 'siswa' ? 'Siswa' : 'Admin';
    });
}

// Add thousands separator to number
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Schedule update for upcoming events
function scheduleNextCheck() {
    // Check for pulang availability at 12:00
    const now = new Date();
    let nextCheck = new Date();
    nextCheck.setHours(12, 0, 0, 0);
    
    if (now > nextCheck) {
        nextCheck.setDate(nextCheck.getDate() + 1);
    }
    
    const timeout = nextCheck - now;
    setTimeout(() => {
        window.location.reload();
    }, timeout);
}
