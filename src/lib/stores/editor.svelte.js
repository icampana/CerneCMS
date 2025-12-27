    import { Editor } from '@tiptap/core';
    import StarterKit from '@tiptap/starter-kit';
    import BubbleMenuExtension from '@tiptap/extension-bubble-menu';
    import FloatingMenuExtension from '@tiptap/extension-floating-menu';
    import ImageExtension from '@tiptap/extension-image';
    import { SvelteNodeViewRenderer } from 'svelte-tiptap';
    import ImageBlock from '../components/blocks/ImageBlock.svelte';

    // Svelte 5 Rune-based Store
    export class EditorStore {
        // $state for reactivity
        title = $state('Untitled Page');
        content = $state('<p>Start writing...</p>');
        editor = $state(null);
        isEditable = $state(true);
        isSaving = $state(false);
        lastSaved = $state(null);

        // Track menu elements
        bubbleMenuElement = $state(null);
        floatingMenuElement = $state(null);

        constructor() {
            // Tiptap is lazily initialized or re-configured when elements are available
            // But usually we init it once.
            // The challenge: Extensions need DOM elements passed in `configure()`.
            // We can init Tiptap *without* them configured, and then update their configuration?
            // Tiptap doesn't easily support dynamic extension configuration updates for these menus.
            // Best pattern for Svelte: Init editor ONLY when we know the DOM and (optionally) menu DOMs?
            // Or use a custom effect.
            // Actually, we can pass null key and configure later? No.

            // Simpler approach: We expose a `init` method that the Component calls when ready.
        }

        init(element, bubbleMenuEl, floatingMenuEl) {
             if (this.editor) return;

             this.editor = new Editor({
                element: element,
                extensions: [
                    StarterKit,
                    ImageExtension.configure({
                        inline: true,
                        allowBase64: true,
                    }).extend({
                        addNodeView() {
                            return SvelteNodeViewRenderer(ImageBlock);
                        }
                    }),
                    BubbleMenuExtension.configure({
                        element: bubbleMenuEl,
                        tippyOptions: { duration: 100 },
                    }),
                    FloatingMenuExtension.configure({
                        element: floatingMenuEl,
                        tippyOptions: { duration: 100 },
                    })
                ],
                content: this.content,
                onUpdate: ({ editor }) => {
                    this.content = editor.getHTML();
                },
                onTransaction: ({ editor }) => {
                    this.editor = editor;
                }
            });
        }

        // Helper to register from menu components if we used the previous pattern
        // But the init pattern above is cleaner (pass all refs from parent).

        destroy() {
             if (this.editor) {
                this.editor.destroy();
                this.editor = null;
            }
        }

        setContent(html) {
             this.content = html;
             if (this.editor) {
                 this.editor.commands.setContent(html);
             }
        }

        async save() {
            if (!this.editor) return;

            this.isSaving = true;
            try {
                // Determine if we are creating or updating (mock ID for now or from context)
                // For this phase, we just create a new page every time
                // or we could try to update if we had an ID.
                // Let's just hit the save endpoint.

                const response = await fetch('/api/pages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: this.title,
                        content: this.editor.getJSON(), // Save JSON, not HTML, for robustness
                        // content: this.editor.getHTML() // or HTML if backend expects it
                        slug: this.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || 'new-page'
                    })
                });

                if (!response.ok) throw new Error('Failed to save');

                const result = await response.json();
                this.lastSaved = new Date();
                console.log('Saved:', result);
                return result;
            } catch (e) {
                console.error('Save error:', e);
                alert('Failed to save page');
            } finally {
                this.isSaving = false;
            }
        }
    }

    export const editorStore = new EditorStore();
