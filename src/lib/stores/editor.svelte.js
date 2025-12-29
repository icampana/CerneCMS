
    import { Editor } from '@tiptap/core';
    import StarterKit from '@tiptap/starter-kit';
    import BubbleMenuExtension from '@tiptap/extension-bubble-menu';
    import Image from '@tiptap/extension-image';
    import { Table } from '@tiptap/extension-table';
    import { TableRow } from '@tiptap/extension-table-row';
    import { TableCell } from '@tiptap/extension-table-cell';
    import { TableHeader } from '@tiptap/extension-table-header';
    import { Youtube } from '@tiptap/extension-youtube';
    import { TextAlign } from '@tiptap/extension-text-align';
    import { HorizontalRule } from '@tiptap/extension-horizontal-rule';
    import CalendarExtension from '../editor/extensions/Calendar.js'; // Import Calendar Extension
    import Grid from '../extensions/Grid';
import Column from '../extensions/Column';
import DragHandle from '@tiptap/extension-drag-handle';
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

        // Block Options Menu State
        blockMenuOpen = $state(false);
        blockMenuTarget = $state(null);
        // Store the block range for menu actions
        currentBlockRange = $state(null);

        // Video Modal State
        videoModalOpen = $state(false);
        videoCallback = $state(null);

        // Toast Notification State
        toastVisible = $state(false);
        toastMessage = $state('');
        toastType = $state('success'); // 'success' or 'error'

        // Settings Drawer State
        settingsDrawerOpen = $state(false);

        // SEO Fields
        metaTitle = $state('');
        metaDescription = $state('');

        // Block Settings Drawer State
        blockSettings = $state({
            isOpen: false,
            type: null, // e.g., 'image'
            attributes: {},
            updateCallback: null
        });

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

        init(element, bubbleMenuEl) {
             if (this.editor) return;

             this.editor = new Editor({
                element: element,
                extensions: [
                    StarterKit,
                Image.extend({
                    addAttributes() {
                        return {
                            ...this.parent?.(),
                            lightbox: {
                                default: false,
                                parseHTML: element => element.getAttribute('data-lightbox') === 'true',
                                renderHTML: attributes => {
                                    if (!attributes.lightbox) return {};
                                    return { 'data-lightbox': 'true' };
                                }
                            }
                        };
                    },
                    addNodeView() {
                        return SvelteNodeViewRenderer(ImageBlock);
                    }
                }).configure({
                    HTMLAttributes: {
                        class: 'rounded-lg max-w-full',
                    },
                }),
                    Grid,
                    Column,
                    HorizontalRule,
                    CalendarExtension, // Add to extensions list
                    Table.configure({
                        resizable: true,
                        HTMLAttributes: {
                            class: 'border-collapse table-auto w-full my-4',
                        },
                    }),
                    TableRow,
                    TableHeader.configure({
                         HTMLAttributes: {
                            class: 'border border-gray-300 bg-gray-50 p-2 font-bold text-left',
                        },
                    }),
                    TableCell.configure({
                        HTMLAttributes: {
                            class: 'border border-gray-300 p-2 relative',
                        },
                    }),
                    Youtube.configure({
                        controls: false,
                        nocookie: true,
                         HTMLAttributes: {
                            class: 'w-full aspect-video rounded-lg shadow-sm',
                        },
                    }),
                    TextAlign.configure({
                        types: ['heading', 'paragraph'],
                    }),
                    DragHandle.configure({
                    render: () => {
                        const element = document.createElement('div');
                        element.classList.add('drag-handle');
                        // Attach click listener for block menu
                        element.addEventListener('click', (e) => {
                             this.handleDragHandleClick(e, element);
                        });
                        return element;
                    },
                }),

                BubbleMenuExtension.configure({
                    element: bubbleMenuEl,
                    tippyOptions: {
                        placement: 'top',
                        maxWidth: 'none',
                        offset: [0, 10],
                        appendTo: document.body,
                    },
                })
            ],
            editorProps: {
                handleDrop: (view, event, slice, moved) => {
                    if (!moved && event.dataTransfer && event.dataTransfer.getData('cerne/type')) {
                        event.preventDefault();

                        const type = event.dataTransfer.getData('cerne/type');
                        const coordinates = view.posAtCoords({ left: event.clientX, top: event.clientY });

                        if (coordinates) {
                            const { pos } = coordinates;

                            // Insert based on type
                            let content;
                            if (type === 'image') {
                                // Insert placeholder first
                                const placeholderUrl = 'https://placehold.co/600x400?text=Select+Image';
                                const tr = view.state.tr.insert(pos, view.state.schema.nodes.image.create({ src: placeholderUrl }));
                                // Select the inserted image
                                const selection = view.state.selection.constructor.near(tr.doc.resolve(pos), 1);
                                tr.setSelection(selection);
                                view.dispatch(tr);

                                // Open Media Library immediately
                                this.openMediaLibrary((url) => {
                                    // Update the selected image (which we just inserted)
                                    this.editor.chain().focus().setImage({ src: url }).run();
                                });
                            } else if (type === 'grid-2') {
                                view.dispatch(view.state.tr.insert(pos, view.state.schema.nodes.grid.create({ colCount: 2 }, [
                                    view.state.schema.nodes.column.create(null, [view.state.schema.nodes.paragraph.create()]),
                                    view.state.schema.nodes.column.create(null, [view.state.schema.nodes.paragraph.create()])
                                ])));
                            } else if (type === 'grid-3') {
                                view.dispatch(view.state.tr.insert(pos, view.state.schema.nodes.grid.create({ colCount: 3 }, [
                                    view.state.schema.nodes.column.create(null, [view.state.schema.nodes.paragraph.create()]),
                                    view.state.schema.nodes.column.create(null, [view.state.schema.nodes.paragraph.create()]),
                                    view.state.schema.nodes.column.create(null, [view.state.schema.nodes.paragraph.create()])
                                ])));
                            } else if (type === 'table') {
                                view.dispatch(view.state.tr.insert(pos, view.state.schema.nodes.table.create(null, [
                                    view.state.schema.nodes.tableRow.create(null, [
                                        view.state.schema.nodes.tableHeader.create(null, view.state.schema.nodes.paragraph.create()),
                                        view.state.schema.nodes.tableHeader.create(null, view.state.schema.nodes.paragraph.create({ text: '' })),
                                        view.state.schema.nodes.tableHeader.create(null, view.state.schema.nodes.paragraph.create({ text: '' }))
                                    ]),
                                    view.state.schema.nodes.tableRow.create(null, [
                                        view.state.schema.nodes.tableCell.create(null, view.state.schema.nodes.paragraph.create()),
                                        view.state.schema.nodes.tableCell.create(null, view.state.schema.nodes.paragraph.create()),
                                        view.state.schema.nodes.tableCell.create(null, view.state.schema.nodes.paragraph.create())
                                    ])
                                ])));
                                // Note: Simple 3x2 table creation manually since insertTable command is transactional on selection
                            } else if (type === 'video') {
                                // Use Modal for better UX and reliability
                                this.openVideoModal((url) => {
                                    // Use the captured view and pos from the closure
                                    const tr = view.state.tr.insert(pos, view.state.schema.nodes.youtube.create({ src: url }));
                                    view.dispatch(tr);
                                });
                            } else if (type === 'divider') {
                                view.dispatch(view.state.tr.insert(pos, view.state.schema.nodes.horizontalRule.create()));
                            } else if (type === 'calendar') {
                                view.dispatch(view.state.tr.insert(pos, view.state.schema.nodes.fullCalendar.create({ showAllCalendars: false })));
                            } else if (type === 'heading') {
                                view.dispatch(view.state.tr.insert(pos, view.state.schema.nodes.heading.create({ level: 2 }, view.state.schema.text('Heading 2'))));
                            } else {
                                view.dispatch(view.state.tr.insert(pos, view.state.schema.nodes.paragraph.create(null, view.state.schema.text('Start writing your text here...'))));
                            }

                            view.focus();
                            return true;
                        }
                    }
                    return false;
                }
            },
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
            // SEO fields
            this.metaTitle = data.meta_title || '';
            this.metaDescription = data.meta_description || '';
            // Decode content if it's an object (from API) or use as is
            // API returns object (node) - Tiptap setContent accepts JSON object or HTML string
            this.setContent(data.content);
        }

        reset() {
            this.id = null;
            this.slug = null;
            this.title = 'Untitled Page';
            this.metaTitle = '';
            this.metaDescription = '';
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
                        slug: this.slug || this.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''),
                        meta_title: this.metaTitle,
                        meta_description: this.metaDescription
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
                this.showToast('Page saved successfully', 'success');
                return result;
            } catch (e) {
                console.error('Save error:', e);
                this.showToast('Failed to save page', 'error');
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

        openVideoModal(callback) {
            this.videoCallback = callback;
            this.videoModalOpen = true;
        }

        closeVideoModal() {
            this.videoModalOpen = false;
            this.videoCallback = null;
        }

        confirmVideo(url) {
            if (this.videoCallback) {
                this.videoCallback(url);
            }
            this.closeVideoModal();
        }

        // Toast Methods
        showToast(message, type = 'success') {
            this.toastMessage = message;
            this.toastType = type;
            this.toastVisible = true;
            // Auto-hide after 3 seconds
            setTimeout(() => {
                this.hideToast();
            }, 3000);
        }

        hideToast() {
            this.toastVisible = false;
        }

        // Settings Drawer Methods
        openSettingsDrawer() {
            this.settingsDrawerOpen = true;
        }

        closeSettingsDrawer() {
            this.settingsDrawerOpen = false;
        }

        // Block Settings Methods
        openBlockSettings(type, attributes, updateCallback) {
            this.blockSettings = {
                isOpen: true,
                type,
                attributes: { ...attributes }, // Clone to avoid direct mutation issues
                updateCallback
            };
        }

        closeBlockSettings() {
            this.blockSettings.isOpen = false;
            this.blockSettings.type = null;
            this.blockSettings.updateCallback = null;
        }

        updateBlockSetting(key, value) {
            this.blockSettings.attributes[key] = value;
            if (this.blockSettings.updateCallback) {
                this.blockSettings.updateCallback({ [key]: value });
            }
        }

        handleDragHandleClick(event, element) {
            // Prevent immediate closing due to outside click logic usually
            event.stopPropagation();

            this.blockMenuTarget = element;
            this.blockMenuOpen = !this.blockMenuOpen;
            this.currentBlockRange = null;

            // Try to set selection to the block associated with the handle
            if (this.editor && element && this.blockMenuOpen) {
                const rect = element.getBoundingClientRect();
                // Check slightly to the right of the handle center to find the node
                const coords = { left: rect.right + 20, top: rect.top + (rect.height / 2) };
                const pos = this.editor.view.posAtCoords(coords);

                if (pos) {
                    const resolved = this.editor.state.doc.resolve(pos.pos);
                    // Find the block range at depth 1 (top-level block)
                    const blockStart = resolved.start(1);
                    const blockEnd = resolved.end(1);
                    const node = resolved.node(1);

                    // Store for later use by menu actions
                    this.currentBlockRange = { start: blockStart, end: blockEnd, node };

                    // Focus the editor (not selecting the whole node, just positioning)
                    this.editor.chain().focus(pos.pos).run();
                }
            }
        }

        closeBlockMenu() {
            this.blockMenuOpen = false;
        }

        duplicateNode() {
            if (!this.editor || !this.currentBlockRange) {
                this.closeBlockMenu();
                return;
            }

            const { end, node } = this.currentBlockRange;
            const json = node.toJSON();

            // Insert after the current block
            this.editor.chain().insertContentAt(end + 1, json).run();
            this.closeBlockMenu();
        }

        deleteNode() {
            if (!this.editor || !this.currentBlockRange) {
                this.closeBlockMenu();
                return;
            }

            const { start, end } = this.currentBlockRange;

            // Delete the entire block range
            this.editor.chain().deleteRange({ from: start - 1, to: end + 1 }).run();
            this.closeBlockMenu();
        }

        clearFormatting() {
            if (!this.editor) {
                this.closeBlockMenu();
                return;
            }

            // Use selectParentNode to select the current block, then clear marks
            this.editor.chain()
                .selectParentNode()
                .unsetAllMarks()
                .run();
            this.closeBlockMenu();
        }

    }

    export const editorStore = new EditorStore();
