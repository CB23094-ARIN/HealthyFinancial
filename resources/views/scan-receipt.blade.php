@extends('layouts.app')

@section('content')
<div class="hf-card mx-auto max-w-xl rounded-2xl p-8">
    <h1 class="hf-title mb-6 text-3xl font-black tracking-tight">Scan Receipt</h1>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('scan-receipt.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Receipt Upload --}}
        <div class="mb-4">
            <label for="receiptInput" class="hf-label-text mb-2">
                Receipt Image
            </label>

            <input
                id="receiptInput"
                type="file"
                name="receipt"
                accept="image/*"
                capture="environment"
                class="hf-input w-full"
                required
            >
        </div>

        {{-- Camera Button --}}
        <div class="mb-4">
            <button
                type="button"
                id="startCamera"
                class="hf-btn-secondary w-full rounded-lg px-4 py-3 font-semibold transition">
                Open Camera
            </button>

            <button
                type="button"
                id="capturePhoto"
                class="hf-btn mt-2 hidden w-full rounded-lg px-4 py-3 font-semibold transition">
                Capture Photo
            </button>
        </div>

        {{-- Camera Preview --}}
        <div id="cameraPanel" class="hidden mb-4 overflow-hidden rounded-lg border border-gray-300">
            <video
                id="cameraPreview"
                class="w-full bg-black"
                autoplay
                playsinline>
            </video>

            <canvas id="receiptCanvas" class="hidden"></canvas>
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="hf-btn w-full rounded-lg px-4 py-3 font-semibold transition">
            Scan & Add
        </button>
    </form>
</div>

<script>
const receiptInput = document.getElementById('receiptInput');
const startCamera = document.getElementById('startCamera');
const capturePhoto = document.getElementById('capturePhoto');
const cameraPanel = document.getElementById('cameraPanel');
const cameraPreview = document.getElementById('cameraPreview');
const receiptCanvas = document.getElementById('receiptCanvas');

let cameraStream = null;

function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }

    cameraPanel.classList.add('hidden');
    capturePhoto.classList.add('hidden');
}

startCamera.addEventListener('click', async () => {

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        receiptInput.click();
        return;
    }

    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: {
                    ideal: 'environment'
                }
            },
            audio: false
        });

        cameraPreview.srcObject = cameraStream;

        cameraPanel.classList.remove('hidden');
        capturePhoto.classList.remove('hidden');

    } catch (error) {
        receiptInput.click();
    }
});

capturePhoto.addEventListener('click', () => {

    const width = cameraPreview.videoWidth;
    const height = cameraPreview.videoHeight;

    if (!width || !height) {
        alert('Camera is not ready yet.');
        return;
    }

    receiptCanvas.width = width;
    receiptCanvas.height = height;

    const context = receiptCanvas.getContext('2d');
    context.drawImage(cameraPreview, 0, 0, width, height);

    receiptCanvas.toBlob((blob) => {

        const file = new File(
            [blob],
            'receipt-photo.jpg',
            { type: 'image/jpeg' }
        );

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        receiptInput.files = dataTransfer.files;

        stopCamera();

    }, 'image/jpeg', 0.9);
});

window.addEventListener('beforeunload', stopCamera);
</script>
@endsection
