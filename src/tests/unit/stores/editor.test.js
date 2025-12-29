import { describe, it, expect, beforeEach } from 'vitest';
import { editor } from '$lib/stores/editor.svelte.js';

describe('Editor Store', () => {
  beforeEach(() => {
    // Reset editor state before each test
    editor.set({
      content: [],
      selection: null
    });
  });

  it('initializes with empty content', () => {
    const state = editor.get();
    expect(state.content).toEqual([]);
    expect(state.selection).toBeNull();
  });

  it('initializes with existing content', () => {
    const initialContent = [
      {
        type: 'paragraph',
        content: [
          { type: 'text', text: 'Hello World' }
        ]
      }
    ];

    editor.set({ content: initialContent, selection: null });
    const state = editor.get();

    expect(state.content).toEqual(initialContent);
  });

  it('adds a new block', () => {
    const newBlock = {
      type: 'paragraph',
      content: [
        { type: 'text', text: 'New paragraph' }
      ]
    };

    editor.update(state => ({
      ...state,
      content: [...state.content, newBlock]
    }));

    const currentState = editor.get();
    expect(currentState.content).toHaveLength(1);
    expect(currentState.content[0]).toEqual(newBlock);
  });

  it('updates block content', () => {
    const initialContent = [
      {
        type: 'paragraph',
        content: [
          { type: 'text', text: 'Original text' }
        ]
      }
    ];

    editor.set({ content: initialContent, selection: null });

    editor.update(state => ({
      ...state,
      content: state.content.map((block, index) =>
        index === 0
          ? { ...block, content: [{ type: 'text', text: 'Updated text' }] }
          : block
      )
    }));

    const currentState = editor.get();
    expect(currentState.content[0].content[0].text).toBe('Updated text');
  });

  it('deletes a block', () => {
    const initialContent = [
      { type: 'paragraph', content: [{ type: 'text', text: 'First' }] },
      { type: 'paragraph', content: [{ type: 'text', text: 'Second' }] },
      { type: 'paragraph', content: [{ type: 'text', text: 'Third' }] }
    ];

    editor.set({ content: initialContent, selection: null });

    editor.update(state => ({
      ...state,
      content: state.content.filter((_, index) => index !== 1)
    }));

    const currentState = editor.get();
    expect(currentState.content).toHaveLength(2);
    expect(currentState.content[0].content[0].text).toBe('First');
    expect(currentState.content[1].content[0].text).toBe('Third');
  });

  it('reorders blocks', () => {
    const initialContent = [
      { type: 'paragraph', content: [{ type: 'text', text: 'First' }] },
      { type: 'paragraph', content: [{ type: 'text', text: 'Second' }] },
      { type: 'paragraph', content: [{ type: 'text', text: 'Third' }] }
    ];

    editor.set({ content: initialContent, selection: null });

    // Move second block to first position
    editor.update(state => {
      const newContent = [...state.content];
      const [moved] = newContent.splice(1, 1);
      newContent.unshift(moved);
      return { ...state, content: newContent };
    });

    const currentState = editor.get();
    expect(currentState.content[0].content[0].text).toBe('Second');
    expect(currentState.content[1].content[0].text).toBe('First');
  });

  it('updates selection', () => {
    editor.update(state => ({
      ...state,
      selection: { from: 0, to: 5 }
    }));

    const currentState = editor.get();
    expect(currentState.selection).toEqual({ from: 0, to: 5 });
  });

  it('clears selection', () => {
    editor.update(state => ({
      ...state,
      selection: { from: 0, to: 5 }
    }));

    editor.update(state => ({
      ...state,
      selection: null
    }));

    const currentState = editor.get();
    expect(currentState.selection).toBeNull();
  });

  it('handles multiple blocks', () => {
    const content = [
      { type: 'paragraph', content: [{ type: 'text', text: 'First' }] },
      { type: 'paragraph', content: [{ type: 'text', text: 'Second' }] },
      { type: 'paragraph', content: [{ type: 'text', text: 'Third' }] }
    ];

    editor.set({ content, selection: null });
    const state = editor.get();

    expect(state.content).toHaveLength(3);
    expect(state.content[0].content[0].text).toBe('First');
    expect(state.content[1].content[0].text).toBe('Second');
    expect(state.content[2].content[0].text).toBe('Third');
  });

  it('serializes to JSON', () => {
    const content = [
      {
        type: 'paragraph',
        content: [{ type: 'text', text: 'Test' }]
      }
    ];

    editor.set({ content, selection: null });
    const state = editor.get();

    const json = JSON.stringify(state.content);
    const parsed = JSON.parse(json);

    expect(parsed).toEqual(content);
  });

  it('handles empty content array', () => {
    editor.set({ content: [], selection: null });
    const state = editor.get();

    expect(state.content).toEqual([]);
    expect(state.content).toHaveLength(0);
  });

  it('preserves immutability when updating', () => {
    const initialContent = [
      { type: 'paragraph', content: [{ type: 'text', text: 'Original' }] }
    ];

    editor.set({ content: initialContent, selection: null });
    const stateBefore = editor.get();

    editor.update(state => ({
      ...state,
      content: [...state.content, { type: 'paragraph', content: [{ type: 'text', text: 'New' }] }]
    }));

    const stateAfter = editor.get();

    expect(stateBefore.content).toHaveLength(1);
    expect(stateAfter.content).toHaveLength(2);
    expect(stateBefore.content).not.toBe(stateAfter.content);
  });
});
