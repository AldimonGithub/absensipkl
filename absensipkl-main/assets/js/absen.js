// Absensi Form Handler
document.addEventListener('DOMContentLoaded', function() {
    updateTime();
    setInterval(updateTime, 1000);
    
    // Handle status change to enable/disable fields
    const statusRadios = document.querySelectorAll('input[name="status"]');
    statusRadios.forEach(radio => {
        radio.addEventListener('change', handleStatusChange);
    });
    
    // Prevent form submission if time < 06:00
    const form = document.querySelector('.absen-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedStatus = document.querySelector('input[name="status"]:checked');
            if (selectedStatus && selectedStatus.value === 'Hadir') {
                const currentHour = new Date();
                if (currentHour.getHours() < 6) {
                    e.preventDefault();
                    alert('Anda tidak bisa melakukan absen sebelum pukul 06:00!');
                }
            }
        });
    }
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

function handleStatusChange() {
    const selectedStatus = document.querySelector('input[name="status"]:checked');
    const keteranganField = document.getElementById('keterangan');
    const fileField = document.getElementById('bukti_file');
    const keteranganRequired = document.getElementById('keterangan-required');
    const fileRequired = document.getElementById('file-required');
    
    if (selectedStatus) {
        if (selectedStatus.value === 'Hadir') {
            // Keterangan dan Bukti nonaktif
            keteranganField.disabled = true;
            fileField.disabled = true;
            keteranganRequired.textContent = '(nonaktif)';
            fileRequired.textContent = '(nonaktif)';
            keteranganField.value = '';
            fileField.value = '';
        } else {
            // Keterangan dan Bukti aktif
            keteranganField.disabled = false;
            fileField.disabled = false;
            keteranganRequired.textContent = '(diperlukan untuk Sakit/Izin/Absen)';
            fileRequired.textContent = '(diperlukan untuk Sakit/Izin/Absen)';
        }
    }
}
