import "./libs/trix";

document.addEventListener('trix-attachment-add', async (event) => {
    const editor = event.target;
    const uploadUrl = editor.closest('[data-trix-upload-url]')?.dataset.trixUploadUrl;
    const attachment = event.attachment;

    if (!uploadUrl || !attachment?.file) {
        return;
    }

    const formData = new FormData();
    formData.append('attachment', attachment.file);

    try {
        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: formData,
        });

        if (!response.ok) {
            throw new Error('Attachment upload failed.');
        }

        const data = await response.json();
        attachment.setAttributes({
            url: data.url,
            href: data.url,
        });
    } catch {
        attachment.remove();
        window.dispatchEvent(new CustomEvent('trix-attachment-upload-failed'));
    }
});
