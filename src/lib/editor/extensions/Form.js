import { Node, mergeAttributes } from '@tiptap/core';
import { SvelteNodeViewRenderer } from 'svelte-tiptap';
import FormBlock from '$lib/components/editor/blocks/FormBlock.svelte';

export default Node.create({
  name: 'formBlock',

  group: 'block',

  atom: true,

  addAttributes() {
    return {
      formId: {
        default: null,
      },
      formSlug: {
        default: null,
      },
       formName: {
        default: null,
      }
    };
  },

  parseHTML() {
    return [
      {
        tag: 'app-form-block',
      },
    ];
  },

  renderHTML({ HTMLAttributes }) {
    return ['app-form-block', mergeAttributes(HTMLAttributes)];
  },

  addNodeView() {
    return SvelteNodeViewRenderer(FormBlock);
  },
});
