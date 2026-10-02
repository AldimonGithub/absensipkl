// QR Code Scanner
let stream = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('qrVideo')) {
        startQRScanner();
    }
});

function startQRScanner() {
    const video = document.getElementById('qrVideo');
    const canvas = document.createElement('canvas');
    const canvasContext = canvas.getContext('2d');
    
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(function(stream) {
            video.srcObject = stream;
            video.setAttribute('playsinline', true);
            video.play();
            
            requestAnimationFrame(function tick() {
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);
                    
                    const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'dontInvert'
                    });
                    
                    if (code) {
                        document.getElementById('qrCodeInput').value = code.data;
                        document.getElementById('qrForm').submit();
                        if (stream) {
                            stream.getTracks().forEach(track => track.stop());
                        }
                        return;
                    }
                }
                requestAnimationFrame(tick);
            });
        })
        .catch(function(err) {
            alert('Tidak bisa mengakses kamera: ' + err);
        });
}

function refreshCaptcha() {
    location.reload();
}
