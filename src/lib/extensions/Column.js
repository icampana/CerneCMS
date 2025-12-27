import { Node, mergeAttributes } from '@tiptap/core';

export default Node.create({
    name: 'column',

    content: 'block+',

    addOptions() {
        return {
            HTMLAttributes: {
                class: 'grid-column flex-1 min-w-0 border border-dashed border-gray-200 p-2 rounded',
            },
        };
    },

    parseHTML() {
        return [
            { tag: 'div[data-type="column"]' },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, { 'data-type': 'column' }), 0];
    },
});
