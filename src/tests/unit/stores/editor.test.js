import { describe, it, expect, beforeEach, vi } from 'vitest';
import { editorStore } from '$lib/stores/editor.svelte.js';

describe('Editor Store', () => {
  beforeEach(() => {
    // Reset editor state before each test
    editorStore.reset();
  });

  it('initializes with default values', () => {
    expect(editorStore.id).toBeNull();
    expect(editorStore.slug).toBeNull();
    expect(editorStore.title).toBe('Untitled Page');
    expect(editorStore.content).toBe('<p>Start writing...</p>');
    expect(editorStore.isEditable).toBe(true);
    expect(editorStore.isSaving).toBe(false);
  });

  it('resets state', () => {
    editorStore.title = 'Modified Title';
    editorStore.slug = 'modified-slug';

    editorStore.reset();

    expect(editorStore.id).toBeNull();
    expect(editorStore.slug).toBeNull();
    expect(editorStore.title).toBe('Untitled Page');
    expect(editorStore.slug).toBeNull();
  });

  it('loads page data', () => {
    const pageData = {
      id: 1,
      title: 'Test Page',
      slug: 'test-page',
      meta_title: 'SEO Title',
      meta_description: 'SEO Description',
      parent_id: null,
      meta_json: { sidebar_override: 'show' },
      content: { type: 'doc', content: [] }
    };

    editorStore.load(pageData);

    expect(editorStore.id).toBe(1);
    expect(editorStore.title).toBe('Test Page');
    expect(editorStore.slug).toBe('test-page');
    expect(editorStore.metaTitle).toBe('SEO Title');
    expect(editorStore.metaDescription).toBe('SEO Description');
    expect(editorStore.parentId).toBeNull();
    expect(editorStore.metaJson).toEqual({ sidebar_override: 'show' });
  });

  it('sets content', () => {
    const html = '<p>Test content</p>';

    editorStore.setContent(html);

    expect(editorStore.content).toBe(html);
  });

  it('shows toast notification', () => {
    editorStore.showToast('Test message', 'error');

    expect(editorStore.toastMessage).toBe('Test message');
    expect(editorStore.toastType).toBe('error');
    expect(editorStore.toastVisible).toBe(true);
  });

  it('hides toast notification', () => {
    editorStore.toastVisible = true;
    editorStore.toastMessage = 'Test';

    editorStore.hideToast();

    expect(editorStore.toastVisible).toBe(false);
  });

  it('opens and closes media library', () => {
    const callback = vi.fn();

    editorStore.openMediaLibrary(callback);

    expect(editorStore.mediaLibraryOpen).toBe(true);
    expect(editorStore.mediaSelectCallback).toBe(callback);

    editorStore.closeMediaLibrary();

    expect(editorStore.mediaLibraryOpen).toBe(false);
    expect(editorStore.mediaSelectCallback).toBeNull();
  });

  it('opens and closes video modal', () => {
    const callback = vi.fn();

    editorStore.openVideoModal(callback);

    expect(editorStore.videoModalOpen).toBe(true);
    expect(editorStore.videoCallback).toBe(callback);

    editorStore.closeVideoModal();

    expect(editorStore.videoModalOpen).toBe(false);
    expect(editorStore.videoCallback).toBeNull();
  });

  it('opens and closes link modal', () => {
    const callback = vi.fn();

    editorStore.openLinkModal(callback);

    expect(editorStore.linkModalOpen).toBe(true);
    expect(editorStore.linkCallback).toBe(callback);

    editorStore.closeLinkModal();

    expect(editorStore.linkModalOpen).toBe(false);
    expect(editorStore.linkCallback).toBeNull();
  });

  it('opens and closes settings drawer', () => {
    editorStore.openSettingsDrawer();

    expect(editorStore.settingsDrawerOpen).toBe(true);

    editorStore.closeSettingsDrawer();

    expect(editorStore.settingsDrawerOpen).toBe(false);
  });

  it('opens and closes block settings', () => {
    const updateCallback = vi.fn();

    editorStore.openBlockSettings('image', { src: 'test.jpg' }, updateCallback);

    expect(editorStore.blockSettings.isOpen).toBe(true);
    expect(editorStore.blockSettings.type).toBe('image');
    expect(editorStore.blockSettings.attributes).toEqual({ src: 'test.jpg' });
    expect(editorStore.blockSettings.updateCallback).toBe(updateCallback);

    editorStore.closeBlockSettings();

    expect(editorStore.blockSettings.isOpen).toBe(false);
    expect(editorStore.blockSettings.type).toBeNull();
  });

  it('updates block setting', () => {
    const updateCallback = vi.fn();

    editorStore.openBlockSettings('image', { src: 'old.jpg' }, updateCallback);
    editorStore.updateBlockSetting('src', 'new.jpg');

    expect(editorStore.blockSettings.attributes.src).toBe('new.jpg');
    expect(updateCallback).toHaveBeenCalledWith({ src: 'new.jpg' });
  });

  it('handles page hierarchy', () => {
    const pageData = {
      id: 1,
      title: 'Parent Page',
      slug: 'parent',
      parent_id: null,
      meta_json: {},
      content: { type: 'doc', content: [] }
    };

    editorStore.load(pageData);

    expect(editorStore.parentId).toBeNull();

    const childData = {
      id: 2,
      title: 'Child Page',
      slug: 'child',
      parent_id: 1,
      meta_json: {},
      content: { type: 'doc', content: [] }
    };

    editorStore.load(childData);

    expect(editorStore.parentId).toBe(1);
  });

  it('handles SEO metadata', () => {
    const pageData = {
      id: 1,
      title: 'Test Page',
      slug: 'test',
      meta_title: 'Custom Title',
      meta_description: 'Custom Description',
      parent_id: null,
      meta_json: {},
      content: { type: 'doc', content: [] }
    };

    editorStore.load(pageData);

    expect(editorStore.metaTitle).toBe('Custom Title');
    expect(editorStore.metaDescription).toBe('Custom Description');
  });

  it('handles block menu state', () => {
    editorStore.blockMenuOpen = false;
    editorStore.blockMenuTarget = null;
    editorStore.currentBlockRange = null;

    expect(editorStore.blockMenuOpen).toBe(false);
    expect(editorStore.blockMenuTarget).toBeNull();
    expect(editorStore.currentBlockRange).toBeNull();
  });

  it('handles saving state', () => {
    expect(editorStore.isSaving).toBe(false);
    expect(editorStore.lastSaved).toBeNull();
  });
});
