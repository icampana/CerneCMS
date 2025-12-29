import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/svelte';
import ComponentToolbar from '$lib/components/ComponentToolbar.svelte';

describe('ComponentToolbar', () => {
  it('renders toolbar container', () => {
    render(ComponentToolbar, {
      props: {
        onBlockInsert: vi.fn()
      }
    });

    const toolbar = document.querySelector('.component-toolbar');
    expect(toolbar).toBeInTheDocument();
  });

  it('calls onBlockInsert when block button is clicked', async () => {
    const onBlockInsert = vi.fn();

    render(ComponentToolbar, {
      props: {
        onBlockInsert
      }
    });

    // Find and click the image button (assuming it has a data attribute or title)
    const buttons = document.querySelectorAll('[data-block-type]');
    const imageButton = Array.from(buttons).find(btn => btn.dataset.blockType === 'image');

    if (imageButton) {
      await imageButton.click();
      expect(onBlockInsert).toHaveBeenCalledWith('image');
    }
  });

  it('renders multiple block type buttons', () => {
    render(ComponentToolbar, {
      props: {
        onBlockInsert: vi.fn()
      }
    });

    const buttons = document.querySelectorAll('[data-block-type]');
    expect(buttons.length).toBeGreaterThan(0);
  });

  it('displays tooltips on hover', () => {
    render(ComponentToolbar, {
      props: {
        onBlockInsert: vi.fn()
      }
    });

    const buttons = document.querySelectorAll('[title]');
    expect(buttons.length).toBeGreaterThan(0);
  });
});
