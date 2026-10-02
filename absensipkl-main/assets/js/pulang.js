// Pulang Form Handler
document.addEventListener('DOMContentLoaded', function() {
    updateTime();
    setInterval(updateTime, 1000);
    updatePulangStatus();
    setInterval(updatePulangStatus, 1000);
});

function updateTime() {
    const now = new Date();
    
    // Update jam terkini
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('jam-terkini').textContent = `${hours}:${minutes}:${seconds}`;
    
    // Update tanggal terkini
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateString = now.toLocaleDateString('id-ID', options);
    document.getElementById('tanggal-terkini').textContent = dateString;
}

function updatePulangStatus() {
    const now = new Date();
    const hours = now.getHours();
    const minutes = now.getMinutes();
    const totalMinutes = hours * 60 + minutes;
    
    const cepatMinutes = 12 * 60 + 0;      // 12:00
    const tepatStart = 15 * 60 + 41;       // 15:41
    const tepatEnd = 16 * 60 + 10;         // 16:10
    const lemburStart = 16 * 60 + 11;      // 16:11
    
    let status = '';
    let badgeClass = '';
    
    if (totalMinutes <= cepatMinutes) {
        status = 'Pulang Cepat';
        badgeClass = 'pulang-cepat';
    } else if (totalMinutes >= tepatStart && totalMinutes <= tepatEnd) {
        status = 'Tepat Waktu';
        badgeClass = 'tepat-waktu';
    } else if (totalMinutes > lemburStart && totalMinutes < 22 * 60) {
        status = 'Lembur';
        badgeClass = 'lembur';
    } else {
        status = 'Di Luar Jam Operasional';
        badgeClass = '';
    }
    
    document.getElementById('pulang-status-text').textContent = status;
    const badge = document.getElementById('pulang-status-badge');
    badge.className = 'badge ' + badgeClass;
    badge.textContent = status;
}
