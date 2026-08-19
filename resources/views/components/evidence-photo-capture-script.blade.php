let evidencePhotoFiles = [];
let evidencePhotoStream = null;

function syncEvidencePhotoFiles() {
    const input = document.getElementById('capturePhotoFiles');
    const transfer = new DataTransfer();
    evidencePhotoFiles.forEach(file => transfer.items.add(file));
    input.files = transfer.files;

    const selection = document.getElementById('capturePhotoSelection');
    selection.innerHTML = '';
    evidencePhotoFiles.forEach((file, index) => {
        const column = document.createElement('div');
        column.className = 'col';
        const card = document.createElement('div');
        card.className = 'border rounded p-1 h-100 bg-white';
        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = file.name;
        image.className = 'w-100 rounded';
        image.style.height = '100px';
        image.style.objectFit = 'cover';
        const details = document.createElement('div');
        details.className = 'd-flex align-items-center justify-content-between gap-1 mt-1';
        const label = document.createElement('small');
        label.className = 'text-truncate';
        label.textContent = file.name;
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn btn-xs btn-outline-danger';
        remove.title = 'Remove photo';
        remove.innerHTML = '<i class="bi bi-x-lg"></i>';
        remove.addEventListener('click', () => {
            evidencePhotoFiles.splice(index, 1);
            syncEvidencePhotoFiles();
        });
        details.append(label, remove);
        card.append(image, details);
        column.appendChild(card);
        selection.appendChild(column);
    });
}

function addEvidencePhotoFiles(files) {
    Array.from(files).forEach(file => {
        const duplicate = evidencePhotoFiles.some(existing => existing.name === file.name && existing.size === file.size && existing.lastModified === file.lastModified);
        if (!duplicate) evidencePhotoFiles.push(file);
    });
    syncEvidencePhotoFiles();
}

async function startEvidenceCamera() {
    const video = document.getElementById('capturePhotoVideo');
    const message = document.getElementById('capturePhotoCameraMessage');

    try {
        evidencePhotoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
        video.srcObject = evidencePhotoStream;
        video.classList.remove('d-none');
        message.classList.add('d-none');
        document.getElementById('takeEvidencePhotoBtn').disabled = false;
        document.getElementById('stopEvidenceCameraBtn').classList.remove('d-none');
    } catch (error) {
        message.textContent = 'Camera access was unavailable. Allow camera permission, or choose photos from your device.';
        message.classList.remove('d-none');
    }
}

function takeEvidencePhoto() {
    const video = document.getElementById('capturePhotoVideo');
    if (!video.videoWidth) return;

    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    canvas.toBlob(blob => {
        if (!blob) return;
        addEvidencePhotoFiles([new File([blob], 'evidence-photo-' + Date.now() + '.jpg', { type: 'image/jpeg', lastModified: Date.now() })]);
    }, 'image/jpeg', 0.9);
}

function stopEvidenceCamera() {
    evidencePhotoStream?.getTracks().forEach(track => track.stop());
    evidencePhotoStream = null;
    const video = document.getElementById('capturePhotoVideo');
    video.srcObject = null;
    video.classList.add('d-none');
    document.getElementById('capturePhotoCameraMessage').classList.remove('d-none');
    document.getElementById('takeEvidencePhotoBtn').disabled = true;
    document.getElementById('stopEvidenceCameraBtn').classList.add('d-none');
}

function resetEvidencePhotoCapture() {
    evidencePhotoFiles = [];
    document.getElementById('capturePhotoFiles').value = '';
    syncEvidencePhotoFiles();
}

document.getElementById('capturePhotoFiles').addEventListener('change', function () {
    addEvidencePhotoFiles(this.files);
});
document.getElementById('capturePhotoModal').addEventListener('hidden.bs.modal', stopEvidenceCamera);
