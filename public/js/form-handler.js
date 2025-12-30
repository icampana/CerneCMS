document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.cerne-form');

    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = form.querySelector('button[type="submit"]');
            const btnText = form.querySelector('.submit-text');
            const spinner = form.querySelector('.loading-spinner');
            const messageEl = form.querySelector('.form-message');

            // Disable button
            btn.disabled = true;
            btnText.classList.add('opacity-0');
            spinner.classList.remove('hidden');
            spinner.classList.add('inline-block');

            // Clear messages
            messageEl.classList.add('hidden');
            messageEl.classList.remove('bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');
            messageEl.textContent = '';

            // reset field errors
            form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
            form.querySelectorAll('.text-red-600').forEach(el => el.remove());

            try {
                const formData = new FormData(form);
                const jsonData = {};
                formData.forEach((value, key) => jsonData[key] = value);

                // Also get CSRF token from hidden input
                const csrfToken = form.querySelector('input[name="csrf_token"]')?.value;
                if (csrfToken) {
                    jsonData['csrf_token'] = csrfToken;
                }

                // Handle checkboxes (if unchecked, they don't appear in FormData, but explicit false is useful?)
                // Actually FormData is standard. We validated them on server.

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken || ''
                    },
                    body: JSON.stringify(jsonData)
                });

                const result = await response.json();

                if (response.ok) {
                    // Success
                    messageEl.textContent = result.message || 'Thank you! Your submission has been received.';
                    messageEl.classList.add('bg-green-100', 'text-green-800');
                    messageEl.classList.remove('hidden');
                    form.reset();
                } else {
                    // Error
                    throw new Error(result.error || 'Something went wrong');

                    if (result.fields) {
                         // Show field specific errors
                         Object.keys(result.fields).forEach(field => {
                             const input = form.querySelector(`[name="${field}"]`);
                             if (input) {
                                 input.classList.add('border-red-500');
                                 // Add error below
                             }
                         });
                    }
                }

            } catch (error) {
                console.error('Submission error:', error);
                messageEl.textContent = error.message;
                messageEl.classList.add('bg-red-100', 'text-red-800');
                messageEl.classList.remove('hidden');
            } finally {
                // Re-enable button
                btn.disabled = false;
                btnText.classList.remove('opacity-0');
                spinner.classList.add('hidden');
                spinner.classList.remove('inline-block');
            }
        });
    });
});
