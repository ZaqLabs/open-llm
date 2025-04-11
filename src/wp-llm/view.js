/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */
import { marked } from 'marked';
/* eslint-disable no-console */
console.log( 'Hello World! (from create-block-wp-llm block)' );
/* eslint-enable no-console */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form#wp-llm-form');
    const textarea = document.getElementById('wp-llm-textarea');
    const processing = document.querySelector('#wp-llm-loading');

    let errorOccurred  = false;

    if (form && textarea) {
        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const message = textarea.value.trim();
            if (!message) return;
            processing.style.display = 'block';

            try {
                const response = await fetch('/wp-json/wp-llm/v1/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': wpApiSettings.nonce
                    },
                    body: JSON.stringify({ message })
                });
                
                const data = await response.json();
                if (data.status === 'success') {
                    const responseP = document.createElement('p');
                    const requestP = document.createElement('p');
                    requestP.style.color = 'red';
                    requestP.textContent = textarea.value;
                    responseP.innerHTML = marked.parse(data.message);
                    textarea.parentNode.insertBefore(requestP, textarea);
                    textarea.parentNode.insertBefore(responseP, textarea);
                    textarea.value = '';
                    
                } else {
                    console.error('Error:', data.message);
                    processing.innerHTML = data.message;
                    errorOccurred = true
                }
            } catch (error) {
                console.error('Error:->', error);
                processing.innerHTML = error.message || String(error);
                errorOccurred = true
            } finally {
                if (!errorOccurred) {
                    processing.style.display = 'none';
                }
                errorOccurred = false;
            }
        });
    }
});
