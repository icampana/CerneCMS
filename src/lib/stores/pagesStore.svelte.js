// Pages Store - State management for Pages section
import { editorStore } from './editor.svelte.js';

export class PagesStore {
    // State
    pages = $state([]);
    loading = $state(false);
    error = $state(null);
    viewMode = $state('grid'); // 'grid' | 'table'
    searchQuery = $state('');
    statusFilter = $state('all');
    selectedIds = $state([]);

    // Load pages from API
    async loadPages() {
        this.loading = true;
        this.error = null;
        try {
            const res = await fetch('/api/pages');
            if (!res.ok) throw new Error('Failed to load pages');
            const data = await res.json();
            this.pages = Array.isArray(data) ? data : (data.data || []);
        } catch (e) {
            this.error = e.message;
            console.error('Error loading pages:', e);
        } finally {
            this.loading = false;
        }
    }

    // Get filtered pages based on search and status filter
    get filteredPages() {
        let filtered = [...this.pages];

        // Apply status filter
        if (this.statusFilter !== 'all') {
            filtered = filtered.filter(page => page.status === this.statusFilter);
        }

        // Apply search filter
        if (this.searchQuery) {
            const query = this.searchQuery.toLowerCase();
            filtered = filtered.filter(page =>
                (page.title && page.title.toLowerCase().includes(query)) ||
                (page.slug && page.slug.toLowerCase().includes(query))
            );
        }

        return filtered;
    }

    // Get status counts
    get statusCounts() {
        return {
            all: this.pages.length,
            published: this.pages.filter(p => p.status === 'published').length,
            draft: this.pages.filter(p => p.status === 'draft').length
        };
    }

    // Set search query
    setSearch(query) {
        this.searchQuery = query;
    }

    // Set status filter
    setStatusFilter(status) {
        this.statusFilter = status;
    }

    // Toggle view mode
    toggleViewMode(mode) {
        this.viewMode = mode;
        // Persist preference to localStorage
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('pagesViewMode', mode);
        }
    }

    // Load view mode from localStorage
    loadViewModePreference() {
        if (typeof localStorage !== 'undefined') {
            const saved = localStorage.getItem('pagesViewMode');
            if (saved && (saved === 'grid' || saved === 'table')) {
                this.viewMode = saved;
            }
        }
    }

    // Toggle selection for a single page
    toggleSelection(id) {
        if (this.selectedIds.includes(id)) {
            this.selectedIds = this.selectedIds.filter(sid => sid !== id);
        } else {
            this.selectedIds = [...this.selectedIds, id];
        }
    }

    // Toggle selection for all pages
    toggleSelectAll() {
        if (this.selectedIds.length === this.filteredPages.length) {
            this.selectedIds = [];
        } else {
            this.selectedIds = this.filteredPages.map(p => p.id);
        }
    }

    // Clear all selections
    clearSelection() {
        this.selectedIds = [];
    }

    // Check if a page is selected
    isSelected(id) {
        return this.selectedIds.includes(id);
    }

    // Check if all pages are selected
    get allSelected() {
        return this.filteredPages.length > 0 && this.selectedIds.length === this.filteredPages.length;
    }

    // Check if some pages are selected (but not all)
    get someSelected() {
        return this.selectedIds.length > 0 && !this.allSelected;
    }

    // Delete a single page
    async deletePage(page) {
        try {
            const res = await fetch(`/api/pages/${page.id}`, {
                method: 'DELETE'
            });
            if (!res.ok) throw new Error('Failed to delete page');

            // Optimistic UI: remove from local state
            this.pages = this.pages.filter(p => p.id !== page.id);
            this.selectedIds = this.selectedIds.filter(id => id !== page.id);

            editorStore.showToast('Page deleted successfully', 'success');
            return true;
        } catch (e) {
            console.error('Error deleting page:', e);
            editorStore.showToast('Failed to delete page', 'error');
            return false;
        }
    }

    // Delete multiple pages
    async deletePages(ids) {
        try {
            // For now, delete one by one (API doesn't support bulk delete yet)
            const promises = ids.map(id =>
                fetch(`/api/pages/${id}`, { method: 'DELETE' })
            );

            await Promise.all(promises);

            // Optimistic UI: remove from local state
            this.pages = this.pages.filter(p => !ids.includes(p.id));
            this.selectedIds = [];

            editorStore.showToast(`${ids.length} page(s) deleted successfully`, 'success');
            return true;
        } catch (e) {
            console.error('Error deleting pages:', e);
            editorStore.showToast('Failed to delete pages', 'error');
            return false;
        }
    }

    // Duplicate a page
    async duplicatePage(page) {
        try {
            const res = await fetch('/api/pages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    title: `${page.title} (Copy)`,
                    content: page.content,
                    slug: `${page.slug}-copy`,
                    status: 'draft'
                })
            });

            if (!res.ok) throw new Error('Failed to duplicate page');

            const result = await res.json();

            // Reload pages to get the new page
            await this.loadPages();

            editorStore.showToast('Page duplicated successfully', 'success');
            return result;
        } catch (e) {
            console.error('Error duplicating page:', e);
            editorStore.showToast('Failed to duplicate page', 'error');
            return false;
        }
    }

    // Navigate to editor
    editPage(page) {
        window.location.hash = `/editor/${page.id}`;
    }

    // Preview page
    previewPage(page) {
        // Open in new tab
        const slug = page.slug || page.id;
        window.open(`/${slug}`, '_blank');
    }
}

export const pagesStore = new PagesStore();
