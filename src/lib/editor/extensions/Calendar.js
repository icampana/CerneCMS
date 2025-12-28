import { Node, mergeAttributes } from '@tiptap/core';
import { SvelteNodeViewRenderer } from 'svelte-tiptap';
import CalendarBlock from '$lib/components/editor/blocks/CalendarBlock.svelte';

export default Node.create({
  name: 'fullCalendar',

  group: 'block',

  atom: true,

  addAttributes() {
    return {
      showAllCalendars: {
        default: false,
      },
    };
  },

  parseHTML() {
    return [
      {
        tag: 'full-calendar',
      },
    ];
  },

  renderHTML({ HTMLAttributes }) {
    return ['full-calendar', mergeAttributes(HTMLAttributes)];
  },

  addNodeView() {
    return SvelteNodeViewRenderer(CalendarBlock);
  },
});
