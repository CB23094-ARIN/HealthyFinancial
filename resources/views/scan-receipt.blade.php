@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl shadow p-6 max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Scan Receipt</h1>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('scan-receipt.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Receipt Upload --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">
                Receipt Image
            </label>

            <input
                id="receiptInput"
                type="file"
                name="receipt"
                accept="image/*"
                capture="environment"
                class="w-full border border-gray-300 rounded-lg p-3"
                required
            >
        </div>

        {{-- Camera Button --}}
        <div class="mb-4">
            <button
                type="button"
                id="startCamera"
                class="w-full border border-emerald-500 text-emerald-700 rounded-lg px-4 py-3 hover:bg-emerald-50 transition">
                Open Camera
            </button>

            <button
                type="button"
                id="capturePhoto"
                class="hidden w-full mt-2 bg-emerald-500 text-white rounded-lg px-4 py-3 hover:bg-emerald-600 transition">
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
            class="w-full bg-emerald-500 text-white rounded-lg px-4 py-3 font-semibold hover:bg-emerald-600 transition">
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