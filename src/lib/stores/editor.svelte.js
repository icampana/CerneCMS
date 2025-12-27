
    import { Editor } from '@tiptap/core';
    import StarterKit from '@tiptap/starter-kit';
    import BubbleMenuExtension from '@tiptap/extension-bubble-menu';
    import FloatingMenuExtension from '@tiptap/extension-floating-menu';
    import Image from '@tiptap/extension-image';
import Grid from '../extensions/Grid';
import Column from '../extensions/Column';
    import { SvelteNodeViewRenderer } from 'svelte-tiptap';
    import ImageBlock from '../components/blocks/ImageBlock.svelte';

    // Svelte 5 Rune-based Store
    export class EditorStore {
        // $state for reactivity
        id = $state(null);
        slug = $state(null);
        title = $state('Untitled Page');
        content = $state('<p>Start writing...</p>');
        editor = $state(null);
        isEditable = $state(true);
        isSaving = $state(false);
        lastSaved = $state(null);

        // Media Library State
        mediaLibraryOpen = $state(false);
        mediaSelectCallback = $state(null);

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
                Image.configure({
                    HTMLAttributes: {
                        class: 'rounded-lg max-w-full',
                    },
                }).extend({ // Re-adding the extend part for SvelteNodeViewRenderer
                    addNodeView() {
                        return SvelteNodeViewRenderer(ImageBlock);
                    }
                }),
                Grid,
                Column,
                SlashCore.configure({
                    suggestion: {
                        items: ({ query }) => {
                            return [
                                { title: 'Heading 1', command: ({ editor, range }) => { editor.chain().focus().deleteRange(range).toggleHeading({ level: 1 }).run() } },
                                { title: 'Heading 2', command: ({ editor, range }) => { editor.chain().focus().deleteRange(range).toggleHeading({ level: 2 }).run() } },
                                { title: 'Bullet List', command: ({ editor, range }) => { editor.chain().focus().deleteRange(range).toggleBulletList().run() } },
                                { title: 'Image', command: ({ editor, range }) => {
                                    // Use proper store reference via closure or context if needed,
                                    // for now standard prompt fallback or integration point
                                    // Actually we will use the same store method if possible,
                                    // but accessing instance methods from here is tricky inside `configure`.
                                    // We might rely on global state or event bus.
                                    // For simplicity in this demo, let's keep it basic or rely on the Floating Menu.
                                    const url = prompt('Image URL');
                                    if(url) editor.chain().focus().deleteRange(range).setImage({ src: url }).run()
                                }},
                                { title: '2 Columns', command: ({ editor, range }) => { editor.chain().focus().deleteRange(range).setGrid().run() } },
                                { title: '3 Columns', command: ({ editor, range }) => { editor.chain().focus().deleteRange(range).setGrid3().run() } }
                            ].filter(item => item.title.toLowerCase().startsWith(query.toLowerCase()));
                        }
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

        load(data) {
            this.id = data.id;
            this.title = data.title;
            this.slug = data.slug;
            // Decode content if it's an object (from API) or use as is
            // API returns object (node) - Tiptap setContent accepts JSON object or HTML string
            this.setContent(data.content);
        }

        reset() {
            this.id = null;
            this.slug = null;
            this.title = 'Untitled Page';
            this.setContent('<p>Start writing...</p>');
        }

        async save() {
            if (!this.editor) return;

            this.isSaving = true;
            try {
                // Determine if we are creating or updating

                const response = await fetch('/api/pages', {
                    method: 'POST', // We still use POST to save (ApiController handles update by slug/logic - we should eventually fix this to use ID)
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: this.id, // Pass ID if we have it
                        title: this.title,
                        content: this.editor.getJSON(),
                        // If we have a slug, keep it, otherwise generate from title
                        slug: this.slug || this.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')
                    })
                });

                if (!response.ok) throw new Error('Failed to save');

                const result = await response.json();
                this.lastSaved = new Date();

                // Update local state with result (e.g. if new ID assigned)
                if (result.data && result.data.id) {
                    this.id = result.data.id;
                    this.slug = result.data.page.slug; // Assuming structure
                }

                console.log('Saved:', result);
                return result;
            } catch (e) {
                console.error('Save error:', e);
                alert('Failed to save page');
            } finally {
                this.isSaving = false;
            }
        }

        openMediaLibrary(callback) {
            this.mediaSelectCallback = callback;
            this.mediaLibraryOpen = true;
        }

        closeMediaLibrary() {
            this.mediaLibraryOpen = false;
            this.mediaSelectCallback = null;
        }

        selectMedia(url) {
            if (this.mediaSelectCallback) {
                this.mediaSelectCallback(url);
            }
            this.closeMediaLibrary();
        }
    }

    export const editorStore = new EditorStore();
