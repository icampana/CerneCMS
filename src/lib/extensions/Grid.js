import { Node, mergeAttributes } from '@tiptap/core';

export default Node.create({
    name: 'grid',

    group: 'block',

    content: 'column+',

    addOptions() {
        return {
            HTMLAttributes: {
                class: 'grid-layout flex gap-4 my-4',
            },
        };
    },

    parseHTML() {
        return [
            { tag: 'div[data-type="grid"]' },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, { 'data-type': 'grid' }), 0];
    },

    addCommands() {
        return {
            setGrid: () => ({ commands }) => {
                return commands.insertContent({
                    type: 'grid',
                    content: [
                        { type: 'column', content: [{ type: 'paragraph' }] },
                        { type: 'column', content: [{ type: 'paragraph' }] }
                    ]
                });
            },
            setGrid3: () => ({ commands }) => {
                return commands.insertContent({
                    type: 'grid',
                    content: [
                        { type: 'column', content: [{ type: 'paragraph' }] },
                        { type: 'column', content: [{ type: 'paragraph' }] },
                        { type: 'column', content: [{ type: 'paragraph' }] }
                    ]
                });
            }
        };
    },
});
