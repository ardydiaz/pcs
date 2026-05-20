<script>
(() => {
    if (window.TableController) {
        return;
    }

    class TableController {
        constructor(root) {
            this.root = root;
            this.tableId = root?.dataset?.tableId;
            this.table = this.tableId ? document.getElementById(this.tableId) : null;
            this.tbody = this.table ? this.table.querySelector('tbody') : null;
            this.searchInput = root.querySelector('[data-table-search]');
            this.lengthSelect = root.querySelector('[data-table-length]');
            this.info = root.querySelector('[data-table-info]');
            this.pagination = root.querySelector('[data-table-pagination]');
            this.currentPage = 1;
            this.filteredRows = [];
            this.rows = [];
            this.searchTerm = '';
            this.rowsPerPage = this.parseRowsPerPage(this.lengthSelect?.value || '10');
            this.emptyRow = null;
            this.emptySearchRow = null;

            if (!this.table || !this.tbody) {
                console.warn('TableController: table or tbody not found for', this.tableId);
                return;
            }

            this.bindEvents();
            this.collectRows();
            this.applyFilters({ resetPage: true });
        }

        bindEvents() {
            if (this.searchInput) {
                this.searchInput.addEventListener('input', () => {
                    this.searchTerm = this.searchInput.value.trim().toLowerCase();
                    this.applyFilters({ resetPage: true });
                });
            }

            if (this.lengthSelect) {
                this.lengthSelect.addEventListener('change', () => {
                    this.rowsPerPage = this.parseRowsPerPage(this.lengthSelect.value);
                    this.applyFilters({ resetPage: true });
                });
            }

            if (this.pagination) {
                this.pagination.addEventListener('click', (event) => {
                    const target = event.target.closest('[data-page]');
                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    if (target.closest('.page-item')?.classList.contains('disabled')) {
                        return;
                    }

                    const page = parseInt(target.dataset.page, 10);
                    if (!Number.isNaN(page)) {
                        this.goToPage(page);
                    }
                });
            }
        }

        parseRowsPerPage(value) {
            if (!value || value === 'all') {
                return Infinity;
            }

            const parsed = parseInt(value, 10);
            return Number.isNaN(parsed) ? Infinity : Math.max(parsed, 1);
        }

        collectRows() {
            if (!this.tbody) {
                this.rows = [];
                this.emptyRow = null;
                return;
            }

            const allRows = Array.from(this.tbody.querySelectorAll('tr'));
            this.emptyRow = allRows.find((row) => row.hasAttribute('data-empty')) || null;
            this.emptySearchRow = allRows.find((row) => row.hasAttribute('data-empty-search')) || null;
            this.rows = allRows.filter((row) => row !== this.emptyRow && row !== this.emptySearchRow && !row.hasAttribute('data-ignore'));
        }

        applyFilters({ resetPage = false } = {}) {
            this.collectRows();

            const term = (this.searchTerm || '').trim();
            if (term) {
                this.filteredRows = this.rows.filter((row) => {
                    const searchSource = row.dataset.search ?? row.textContent;
                    return searchSource.toLowerCase().includes(term);
                });
            } else {
                this.filteredRows = [...this.rows];
            }

            if (resetPage) {
                this.currentPage = 1;
            }

            const totalPages = this.getTotalPages();
            if (this.currentPage > totalPages) {
                this.currentPage = totalPages;
            }

            this.render();
        }

        getTotalPages() {
            if (this.rowsPerPage === Infinity) {
                return this.filteredRows.length === 0 ? 1 : 1;
            }

            const total = Math.ceil(this.filteredRows.length / this.rowsPerPage);
            return total > 0 ? total : 1;
        }

        goToPage(page) {
            if (Number.isNaN(page)) {
                return;
            }

            const totalPages = this.getTotalPages();
            if (page < 1 || page > totalPages || page === this.currentPage) {
                return;
            }

            this.currentPage = page;
            this.render();
        }

        render() {
            this.rows.forEach((row) => {
                row.style.display = 'none';
            });

            const hasRows = this.rows.length > 0;
            const noSearchResults = hasRows && this.filteredRows.length === 0;

            if (this.emptyRow) {
                this.emptyRow.style.display = hasRows ? 'none' : '';
            }

            if (this.emptySearchRow) {
                this.emptySearchRow.style.display = noSearchResults ? '' : 'none';
            }

            let visibleRows;
            if (this.rowsPerPage === Infinity) {
                visibleRows = [...this.filteredRows];
            } else {
                const startIndex = (this.currentPage - 1) * this.rowsPerPage;
                const endIndex = startIndex + this.rowsPerPage;
                visibleRows = this.filteredRows.slice(startIndex, endIndex);
            }

            visibleRows.forEach((row) => {
                row.style.display = '';
            });

            const totalEntries = this.filteredRows.length;
            const startEntry = totalEntries === 0
                ? 0
                : this.rowsPerPage === Infinity
                    ? 1
                    : (this.currentPage - 1) * this.rowsPerPage + 1;
            const endEntry = this.rowsPerPage === Infinity
                ? totalEntries
                : Math.min(this.currentPage * this.rowsPerPage, totalEntries);

            this.lastStats = {
                total: this.rows.length,
                filtered: totalEntries,
                start: totalEntries === 0 ? 0 : startEntry,
                end: totalEntries === 0 ? 0 : endEntry,
                page: this.currentPage,
                rowsPerPage: this.rowsPerPage,
            };

            this.updateInfo();
            this.updatePagination();
            this.emitUpdate();
        }

        updateInfo() {
            if (!this.info) {
                return;
            }

            const { start, end, filtered } = this.lastStats;
            this.info.textContent = `Showing ${start} to ${end} of ${filtered} entries`;
        }

        updatePagination() {
            if (!this.pagination) {
                return;
            }

            const totalPages = this.getTotalPages();
            if (this.rowsPerPage === Infinity || totalPages <= 1 || this.filteredRows.length === 0) {
                this.pagination.innerHTML = '';
                this.pagination.classList.add('d-none');
                return;
            }

            this.pagination.classList.remove('d-none');
            const createPageItem = (label, page, disabled = false, active = false, isIcon = false) => {
                const classes = ['page-item'];
                if (disabled) classes.push('disabled');
                if (active) classes.push('active');

                const icon = isIcon ? `<i class="bx ${label}"></i>` : label;

                return `
                    <li class="${classes.join(' ')}">
                        <a class="page-link" href="#" data-page="${page}">${icon}</a>
                    </li>
                `;
            };

            let html = '';
            html += createPageItem('bx-chevron-left', this.currentPage - 1, this.currentPage === 1, false, true);

            const items = [];
            const startPage = Math.max(1, this.currentPage - 2);
            const endPage = Math.min(totalPages, this.currentPage + 2);

            if (startPage > 1) {
                items.push(createPageItem('1', 1, false, this.currentPage === 1));
                if (startPage > 2) {
                    items.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
            }

            for (let page = startPage; page <= endPage; page += 1) {
                items.push(createPageItem(String(page), page, false, page === this.currentPage));
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    items.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
                items.push(createPageItem(String(totalPages), totalPages, false, this.currentPage === totalPages));
            }

            html += items.join('');
            html += createPageItem('bx-chevron-right', this.currentPage + 1, this.currentPage === totalPages, false, true);

            this.pagination.innerHTML = html;
        }

        emitUpdate() {
            const event = new CustomEvent('table:updated', {
                detail: { ...this.lastStats, tableId: this.tableId },
            });
            this.root.dispatchEvent(event);
        }

        refresh() {
            this.applyFilters({ resetPage: false });
        }
    }

    window.TableController = TableController;
    window.tableControllers = window.tableControllers || {};
})();
</script>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/components/table-controller-script.blade.php ENDPATH**/ ?>