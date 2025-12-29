import { Node, mergeAttributes } from '@tiptap/core';
import { SvelteNodeViewRenderer } from 'svelte-tiptap';
import CTABlock from '$lib/components/editor/blocks/CTABlock.svelte';

export default Node.create({
  name: 'cta',

  group: 'block',

  atom: true,

  addAttributes() {
    return {
      layout: {
        default: 'centered',
      },
      title: {
        default: 'Ready to get started?',
      },
      subtitle: {
        default: 'Join thousands of satisfied customers today.',
      },
      buttonText: {
        default: 'Get Started',
      },
      buttonUrl: {
        default: '#',
      },
      backgroundImage: {
        default: '',
      },
      textColor: {
        default: 'auto', // auto, light, dark
      },
    };
  },

  parseHTML() {
    return [
      {
        tag: 'div[data-cta]',
      },
    ];
  },

  renderHTML({ node }) {
    const { layout, title, subtitle, buttonText, buttonUrl, backgroundImage, textColor } = node.attrs;

    // Determine text color classes
    const isHeroOrBg = layout === 'hero' || backgroundImage;
    const autoTextColor = isHeroOrBg ? 'text-white' : 'text-gray-900';
    const textColorClass = textColor === 'light' ? 'text-white' : textColor === 'dark' ? 'text-gray-900' : autoTextColor;

    // Layout classes
    const layoutClasses =
      layout === 'centered' ? 'items-center text-center' :
      layout === 'split' ? 'md:flex-row md:items-center md:justify-between' :
      layout === 'hero' ? 'items-center text-center py-20 min-h-[400px]' : '';

    // Background classes
    const bgClasses = !backgroundImage && layout !== 'hero' ? 'bg-gray-50' :
                      layout === 'hero' && !backgroundImage ? 'bg-gradient-to-br from-blue-600 to-blue-800' : '';

    const bgStyle = backgroundImage ? `background-image: url(${backgroundImage}); background-size: cover; background-position: center;` : '';

    // Button classes - lighter for dark backgrounds
    const btnClasses = isHeroOrBg ? 'bg-white text-gray-900 hover:bg-gray-100' : 'bg-blue-600 text-white hover:bg-blue-700';

    return ['div', {
      'data-cta': '',
      class: 'cta-wrapper my-8',
      style: 'margin: 2rem 0;'
    }, [
      'div', {
        class: `cta-container rounded-2xl overflow-hidden relative shadow-lg transition-all duration-300 ${bgClasses}`,
        style: bgStyle
      },
      ...(backgroundImage || layout === 'hero' ? [[
        'div', {
          class: 'absolute inset-0 bg-black/40',
          style: 'pointer-events: none;'
        }
      ]] : []),
      [
        'div', {
          class: `relative z-10 p-8 md:p-12 flex flex-col gap-6 ${layoutClasses} ${textColorClass}`
        },
        [
          'div', { class: 'flex flex-col gap-4 max-w-2xl' },
          ['h2', { class: layout === 'hero' ? 'text-4xl md:text-5xl font-bold' : 'text-3xl md:text-4xl font-bold' }, title],
          ['p', { class: layout === 'hero' ? 'text-xl opacity-90' : 'text-lg opacity-90' }, subtitle]
        ],
        [
          'div', { class: 'flex-shrink-0' },
          ['a', {
            href: buttonUrl,
            class: `inline-block px-6 py-3 rounded-lg font-semibold transition-transform hover:scale-105 active:scale-95 ${btnClasses}`
          }, buttonText]
        ]
      ]
    ]];
  },

  addNodeView() {
    return SvelteNodeViewRenderer(CTABlock);
  },
});
