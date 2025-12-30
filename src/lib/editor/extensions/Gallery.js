import { Node, mergeAttributes } from '@tiptap/core';
import { SvelteNodeViewRenderer } from 'svelte-tiptap';
import GalleryBlock from '$lib/components/editor/blocks/GalleryBlock.svelte';

export default Node.create({
  name: 'gallery',

  group: 'block',

  atom: true,

  addAttributes() {
    return {
      layout: {
        default: 'standard',
      },
      showCaptions: {
        default: false,
      },
      autoplay: {
        default: true,
      },
      autoplaySpeed: {
        default: 3000,
      },
      gap: {
        default: 8,
      },
      columns: {
        default: 3,
      },
      images: {
        default: [],
      },
    };
  },

  parseHTML() {
    return [
      {
        tag: 'div[data-gallery]',
      },
    ];
  },

  renderHTML({ node }) {
    const { layout, showCaptions, autoplay, autoplaySpeed, gap, columns } = node.attrs;

    return ['div', {
      'data-gallery': '',
      'data-layout': layout,
      'data-show-captions': showCaptions,
      'data-autoplay': autoplay,
      'data-autoplay-speed': autoplaySpeed,
      'data-gap': gap,
      'data-columns': columns,
      class: 'gallery-wrapper my-8',
      style: 'margin: 2rem 0;'
    }];
  },

  addNodeView() {
    return SvelteNodeViewRenderer(GalleryBlock);
  },
});
