
import { Node, mergeAttributes } from '@tiptap/core';
import { SvelteNodeViewRenderer } from 'svelte-tiptap';
import ImageUploadBlock from '../../components/blocks/ImageUploadBlock.svelte';

export default Node.create({
    name: 'imageUpload',

    group: 'block',

    atom: true,

    // It's draggable
    draggable: true,

    parseHTML() {
        return [
            {
                tag: 'div[data-type="image-upload"]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, { 'data-type': 'image-upload' })];
    },

    addNodeView() {
        return SvelteNodeViewRenderer(ImageUploadBlock);
    },

    addCommands() {
        return {
            setImageUpload: () => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                });
            },
        };
    },
});
