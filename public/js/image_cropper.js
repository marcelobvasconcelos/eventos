document.addEventListener('DOMContentLoaded', function () {
    let cropper;
    const modalElement = document.getElementById('cropperModal');
    const imageElement = document.getElementById('cropperImage');
    const cropBtn = document.getElementById('cropBtn');
    let currentInput = null;
    let originalFileName = '';

    // Initialize Bootstrap Modal
    const modal = new bootstrap.Modal(modalElement);

    // Find all image inputs
    const inputs = document.querySelectorAll('input[type="file"][accept*="image"]');

    inputs.forEach(input => {
        input.addEventListener('change', function (e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                originalFileName = file.name;
                currentInput = e.target;

                const reader = new FileReader();
                reader.onload = function (evt) {
                    imageElement.src = evt.target.result;
                    modal.show();
                };
                reader.readAsDataURL(file);

                // Reset input value to allow selecting same file again if cancelled
                e.target.value = '';
            }
        });
    });

    modalElement.addEventListener('shown.bs.modal', function () {
        cropper = new Cropper(imageElement, {
            aspectRatio: 16 / 9, // Format for Carousel
            viewMode: 2, // Restrict crop box to not exceed image
            autoCropArea: 1, // Start with full image if possible
            movable: true,
            zoomable: true,
            scalable: false,
        });
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        imageElement.src = '';
    });

    cropBtn.addEventListener('click', function () {
        if (cropper && currentInput) {
            // Get cropped canvas
            const canvas = cropper.getCroppedCanvas({
                width: 1280, // Target reasonable width
                height: 720,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob(function (blob) {
                // Create new File object
                const newFile = new File([blob], originalFileName, {
                    type: "image/jpeg",
                    lastModified: new Date().getTime()
                });

                // Create DataTransfer to update input files
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(newFile);
                currentInput.files = dataTransfer.files;

                // Trigger manual change event?? No, might loop.
                // Just update preview if exists
                // Try to find a sibling preview or generic preview logic?
                // Or just trust the backend upload.

                // Optional: Update a preview img sibling if it exists?
                // The edit form has a preview. Let's try to update it if it exists near the input.
                // Assuming preview img has class 'img-thumbnail' or is inside a div nearby.

                // Basic visual feedback that file is selected
                // (Browser already shows filename, and we updated input files so it should show)

                modal.hide();
            }, 'image/jpeg', 0.9);
        }
    });
});
