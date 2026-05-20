<?php
    $facultyPayload = $faculties
        ->sortByDesc(function ($faculty) {
            return $faculty->created_at ?? $faculty->id ?? 0;
        })
        ->map(function ($faculty) {
            $facultyUser = optional($faculty->user);

            return [
                'id' => $faculty->id,
                'user_id' => $faculty->user_id,
                'employee_no' => $faculty->employee_no,
                'department' => $faculty->department,
                'job_title' => $faculty->job_title ?? $facultyUser->job_title,
                'user' => [
                    'id' => $facultyUser->id,
                    'name' => $facultyUser->name ?? $faculty->name ?? 'Unknown',
                    'email' => $facultyUser->email,
                    'job_title' => $facultyUser->job_title,
                ],
            ];
        })->values();

    $registeredUserIds = $faculties
        ->pluck('user_id')
        ->filter()
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();

    $eligibleUsers = $users->filter(function ($user) use ($registeredUserIds) {
        if (strtolower($user->role ?? '') !== 'faculty') {
            return false;
        }
        $userId = (int) ($user->id ?? 0);
        return $userId && !$registeredUserIds->contains($userId);
    });

    $userOptions = $eligibleUsers->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name ?? 'Unknown',
            'email' => $user->email,
        ];
    })->values();

    $jobTitleOptions = collect()
        ->merge($faculties->map(fn($faculty) => $faculty->job_title ?? optional($faculty->user)->job_title))
        ->merge($users->map(fn($user) => $user->job_title))
        ->map(fn($value) => trim((string) $value))
        ->filter(fn($value) => $value !== '')
        ->unique()
        ->sort()
        ->values();

    $predefinedDepartments = [
        'Academic Department',
        'Admissions and Financial Aid Department',
        'Basic Education',
        'Campus Development Department',
        'College of Arts & Sciences',
        'College of Dentistry',
        'College of Medical Technology',
        'College of Medicine',
        'College of Nursing',
        'College of Optometry',
        'College of Pharmacy',
        'College of Physical Therapy',
        'Executive Vice Chair',
        'Executive Vice President',
        'External Affairs Office',
        'Finance Department',
        'Human Resource Department',
        'Information Technology Department',
        'Institute of Education',
        'Institutional Research Office',
        'Internal Audit Office',
        'Lead Institute',
        'Library Services Department',
        'Marketing Department',
        'Office of the President',
        'Office of the Registrar',
        'Quality Assurance Office',
        'Research Ethics Office',
        'School of Business and Management',
        'Student Affairs Services',
    ];

    $departmentSelectOptions = collect($predefinedDepartments)
        ->merge($faculties->flatMap(function ($faculty) {
            return collect(explode(',', $faculty->department ?? ''))
                ->map(function ($value) {
                    return trim($value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                });
        }))
        ->merge($users->flatMap(function ($user) {
            return collect(explode(',', $user->department ?? ''))
                ->map(function ($value) {
                    return trim($value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                });
        }))
        ->unique()
        ->sort()
        ->values();

    $container = 'container-xxl';
    $accessLevels = collect(auth()->user()?->access_level ?? []);
    $isAdmin = auth()->user()?->role === 'Admin';
    $canManageFaculties = $isAdmin || $accessLevels->contains('Manage Faculties');
    $canAdd = $canManageFaculties;
    $canEdit = $canManageFaculties;
    $canDelete = $isAdmin;
    $showDeleteDisabled = !$isAdmin && $canManageFaculties;
    $canImport = $canManageFaculties;
    $canImportAll = $canManageFaculties;
?>

<?php $__env->startSection('title', 'Data Management - Faculties'); ?>

<!-- Section for Page Styles component -->
<?php echo $__env->make('content.data-management.partials.faculties.page-style', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- Include the page-specific styles for the faculties data management page -->

<?php $__env->startSection('page-script'); ?>
    <?php echo $__env->make('components.table-controller-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
        const PILL_PALETTES = {
            purple: [{ bg: '#e4c7ff', color: '#4c1d95' }],
            blue: [{ bg: '#d3e2ff', color: '#1d4ed8' }],
            green: [{ bg: '#d1f9e0', color: '#047857' }],
            gray: [{ bg: '#e3e8f1', color: '#475569' }],
        };
        function getPillColor(value, paletteName) {
            const palette = PILL_PALETTES[paletteName];
            if (!palette || !palette.length) {
                return null;
            }
            return palette[0];
        }

        function stylePillElement(element) {
            const paletteName = element.dataset.pillPalette;
            if (!paletteName) {
                return;
            }
            const color = getPillColor(element.dataset.pillValue || element.textContent, paletteName);
            if (!color) {
                return;
            }
            element.style.setProperty('--pill-bg', color.bg);
            element.style.setProperty('--pill-color', color.color);
        }

        function applyPillPalettes(root = document) {
            const scope = root instanceof Element ? root : document.body;
            scope.querySelectorAll('[data-pill-palette]').forEach(stylePillElement);
        }

        const facultyPermissions = {
            canAdd: <?php echo json_encode($canAdd, 15, 512) ?>,
            canEdit: <?php echo json_encode($canEdit, 15, 512) ?>,
            canDelete: <?php echo json_encode($canDelete, 15, 512) ?>,
            showDeleteDisabled: <?php echo json_encode($showDeleteDisabled, 15, 512) ?>,
            canImport: <?php echo json_encode($canImport, 15, 512) ?>,
            canImportAll: <?php echo json_encode($canImportAll, 15, 512) ?>,
        };

        document.addEventListener('DOMContentLoaded', () => {
            initUserDropdowns();
            initDepartmentMultiselects();

            window.facultyPage = new FacultyPage({
                faculties: <?php echo json_encode($facultyPayload, 15, 512) ?>,
                users: <?php echo json_encode($userOptions, 15, 512) ?>,
            });
        });

        function initUserDropdowns() {
            const dropdowns = document.querySelectorAll('[data-user-dropdown]');
            if (!dropdowns.length) {
                return;
            }

            dropdowns.forEach((dropdown) => {
                if (dropdown.dataset.dropdownInitialized === 'true') {
                    return;
                }
                dropdown.dataset.dropdownInitialized = 'true';

                const hiddenInput = dropdown.querySelector('input[name="user_id"]');
                const nameInput = dropdown.querySelector('[data-user-name-input]');
                const listWrapper = dropdown.querySelector('[data-user-list]');
                const menu = dropdown.querySelector('.dropdown-menu');
                let selectedName = '';
                let ignoreBlurClose = false;
                let pointerSelection = false;

                const resetSelection = () => {
                    if (hiddenInput) {
                        hiddenInput.value = '';
                    }
                    if (nameInput) {
                        nameInput.value = '';
                    }
                    selectedName = '';
                };

                const getOptions = () => Array.from(dropdown.querySelectorAll('[data-user-option]'));

                const applySearchFilter = () => {
                    const term = nameInput ? nameInput.value.trim().toLowerCase() : '';
                    const hasTerm = term !== '';
                    let matchesCount = 0;

                    getOptions().forEach((option) => {
                        const name = option.dataset.userNameLower || '';
                        const email = option.dataset.userEmailLower || '';
                        const matches = !hasTerm || name.includes(term) || email.includes(term);
                        option.classList.toggle('d-none', !matches);
                        if (matches) {
                            matchesCount += 1;
                        }
                    });

                    if (listWrapper) {
                        listWrapper.classList.toggle('is-unlimited', hasTerm);
                    }
                    if (menu) {
                        menu.classList.toggle('d-none', hasTerm && matchesCount === 0);
                    }
                };

                const openDropdown = () => {
                    if (menu) {
                        menu.classList.add('show');
                    }
                    dropdown.classList.add('show');
                };

                const closeDropdown = () => {
                    if (menu) {
                        menu.classList.remove('show');
                    }
                    dropdown.classList.remove('show');
                };

                const selectOption = (option) => {
                    if (!option) {
                        return;
                    }
                    if (hiddenInput) {
                        hiddenInput.value = option.dataset.userId || '';
                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (nameInput) {
                        nameInput.value = option.dataset.userName || '';
                        selectedName = nameInput.value.trim();
                    }
                    closeDropdown();
                };

                if (listWrapper) {
                    listWrapper.addEventListener('pointerdown', (event) => {
                        const option = event.target.closest('[data-user-option]');
                        if (!option) {
                            return;
                        }
                        pointerSelection = true;
                        ignoreBlurClose = true;
                        event.preventDefault();
                        selectOption(option);
                        setTimeout(() => {
                            pointerSelection = false;
                            ignoreBlurClose = false;
                        }, 0);
                    });
                    listWrapper.addEventListener('click', (event) => {
                        if (pointerSelection) {
                            return;
                        }
                        const option = event.target.closest('[data-user-option]');
                        if (!option) {
                            return;
                        }
                        event.preventDefault();
                        selectOption(option);
                    });
                }

                if (nameInput) {
                    nameInput.addEventListener('input', () => {
                        const currentName = nameInput.value.trim();
                        if (currentName !== selectedName) {
                            selectedName = '';
                            if (hiddenInput) {
                                hiddenInput.value = '';
                                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                        applySearchFilter();
                        openDropdown();
                    });
                    nameInput.addEventListener('focus', () => {
                        applySearchFilter();
                        openDropdown();
                    });
                    nameInput.addEventListener('blur', () => {
                        setTimeout(() => {
                            if (ignoreBlurClose) {
                                return;
                            }
                            if (dropdown.contains(document.activeElement)) {
                                return;
                            }
                            closeDropdown();
                        }, 120);
                    });
                    nameInput.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            closeDropdown();
                        }
                    });
                }

                document.addEventListener('click', (event) => {
                    if (!dropdown.contains(event.target)) {
                        closeDropdown();
                    }
                });

                const parentForm = dropdown.closest('form');
                if (parentForm) {
                    parentForm.addEventListener('reset', () => {
                        setTimeout(() => {
                            resetSelection();
                            applySearchFilter();
                        }, 0);
                    });
                }

                const initialValue = hiddenInput?.value?.trim();
                if (initialValue) {
                    const option = getOptions().find((opt) => opt.dataset.userId === initialValue);
                    if (option && nameInput) {
                        nameInput.value = option.dataset.userName || option.textContent.trim();
                        selectedName = nameInput.value.trim();
                    } else {
                        resetSelection();
                    }
                } else {
                    resetSelection();
                }

                applySearchFilter();

                dropdown.__userDropdown = {
                    applyFilter: applySearchFilter,
                    resetSelection,
                    setName: (text) => {
                        if (!nameInput) {
                            return;
                        }
                        nameInput.value = text;
                        selectedName = text.trim();
                    },
                };
            });
        }

        function initDepartmentMultiselects() {
            const multiselects = document.querySelectorAll('[data-department-multiselect]');
            multiselects.forEach((multiselect) => {
                if (multiselect.departmentMultiselectApi) {
                    return;
                }
                setupDepartmentMultiselect(multiselect);
            });
        }

        function setupDepartmentMultiselect(multiselect) {
            const placeholder = multiselect.querySelector('[data-department-placeholder]');
            const chipsContainer = multiselect.querySelector('[data-department-selected]');
            const inputsContainer = multiselect.querySelector('[data-department-inputs]');
            const hiddenInput = multiselect.querySelector('[data-department-input]');
            const searchInput = multiselect.querySelector('[data-department-search]');
            const listContainer = multiselect.querySelector('[data-department-list]');
            const optionElements = Array.from(multiselect.querySelectorAll('[data-department-option]'));

            const options = [];
            const selected = new Set();

            const normalizeValues = (value) => {
                if (Array.isArray(value)) {
                    return value.map((entry) => String(entry ?? '').trim()).filter((entry) => entry !== '');
                }
                return String(value ?? '')
                    .split(',')
                    .map((entry) => entry.trim())
                    .filter((entry) => entry !== '');
            };

            const ensureOption = (value) => {
                const normalized = String(value ?? '').trim();
                if (!normalized || options.some((option) => option.value === normalized)) {
                    return;
                }
                if (!listContainer) {
                    return;
                }
                const label = document.createElement('label');
                label.className = 'department-multiselect-option';
                label.dataset.departmentOption = '';
                label.dataset.value = normalized;
                label.dataset.label = normalized;
                label.dataset.search = normalized.toLowerCase();

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'form-check-input';
                checkbox.dataset.departmentCheckbox = '';
                checkbox.value = normalized;

                const span = document.createElement('span');
                span.textContent = normalized;

                label.appendChild(checkbox);
                label.appendChild(span);
                listContainer.appendChild(label);

                registerOption(label);
            };

            const syncHiddenInput = () => {
                if (hiddenInput) {
                    hiddenInput.value = Array.from(selected).join(', ');
                }
                if (inputsContainer) {
                    inputsContainer.innerHTML = '';
                    selected.forEach((value) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'department[]';
                        input.value = value;
                        inputsContainer.appendChild(input);
                    });
                }
            };

            const syncChips = () => {
                if (!chipsContainer) {
                    return;
                }
                chipsContainer.innerHTML = '';
                selected.forEach((value) => {
                    const chip = document.createElement('span');
                    chip.className = 'department-chip';
                    chip.innerHTML = `
                        <span>${value}</span>
                        <button type="button" class="department-chip-remove" data-department-remove="${value}" aria-label="Remove ${value}">&times;</button>
                    `;
                    chipsContainer.appendChild(chip);
                });
                if (placeholder) {
                    placeholder.classList.toggle('d-none', selected.size > 0);
                }
            };

            const syncCheckboxes = () => {
                options.forEach(({ value, checkbox, element }) => {
                    const isSelected = selected.has(value);
                    if (checkbox) {
                        checkbox.checked = isSelected;
                    }
                    element.classList.toggle('is-selected', isSelected);
                });
            };

            const applySearchFilter = () => {
                const term = (searchInput?.value || '').trim().toLowerCase();
                const hasTerm = term !== '';
                options.forEach(({ element, searchValue }) => {
                    const matches = !hasTerm || (searchValue || '').includes(term);
                    element.classList.toggle('d-none', !matches);
                });
            };

            const setSelectedValues = (values) => {
                const normalized = normalizeValues(values);
                selected.clear();
                normalized.forEach((value) => {
                    if (value) {
                        ensureOption(value);
                        selected.add(value);
                    }
                });
                syncHiddenInput();
                syncChips();
                syncCheckboxes();
            };

            const toggleValue = (value) => {
                if (!value) {
                    return;
                }
                const key = String(value);
                if (selected.has(key)) {
                    selected.delete(key);
                } else {
                    ensureOption(key);
                    selected.add(key);
                }
                syncHiddenInput();
                syncChips();
                syncCheckboxes();
            };

            const handleChipRemoval = (event) => {
                const removeBtn = event.target.closest('[data-department-remove]');
                if (!removeBtn) {
                    return;
                }
                event.preventDefault();
                event.stopImmediatePropagation();
                const value = removeBtn.dataset.departmentRemove;
                if (value && selected.has(value)) {
                    selected.delete(value);
                    syncHiddenInput();
                    syncChips();
                    syncCheckboxes();
                }
            };

            const registerOption = (element) => {
                const value = element.dataset.value ?? '';
                const label = element.dataset.label ?? value;
                const searchValue = element.dataset.search ?? label.toLowerCase();
                const checkbox = element.querySelector('[data-department-checkbox]');
                const option = { element, value, label, searchValue, checkbox };
                options.push(option);

                element.addEventListener('click', (event) => {
                    if (event.target instanceof HTMLInputElement) {
                        return;
                    }
                    event.preventDefault();
                    event.stopPropagation();
                    toggleValue(value);
                });

                if (checkbox) {
                    checkbox.addEventListener('change', (event) => {
                        event.stopPropagation();
                        if (event.target.checked) {
                            selected.add(String(value));
                        } else {
                            selected.delete(String(value));
                        }
                        syncHiddenInput();
                        syncChips();
                        syncCheckboxes();
                    });
                }
            };

            optionElements.forEach(registerOption);
            chipsContainer?.addEventListener('click', handleChipRemoval);
            chipsContainer?.addEventListener('mousedown', handleChipRemoval);

            if (searchInput) {
                searchInput.addEventListener('input', applySearchFilter);
            }

            multiselect.addEventListener('shown.bs.dropdown', () => {
                if (searchInput) {
                    searchInput.value = '';
                    applySearchFilter();
                    searchInput.focus();
                }
            });

            const parentForm = multiselect.closest('form');
            if (parentForm) {
                parentForm.addEventListener('reset', () => {
                    setTimeout(() => {
                        setSelectedValues([]);
                        if (searchInput) {
                            searchInput.value = '';
                            applySearchFilter();
                        }
                    }, 0);
                });
            }

            applySearchFilter();
            syncChips();
            syncCheckboxes();
            syncHiddenInput();

            multiselect.departmentMultiselectApi = {
                setSelected: setSelectedValues,
                getSelected: () => Array.from(selected),
                reset: () => setSelectedValues([]),
            };
        }

        class FacultyPage {
            constructor({ faculties, users }) {
                this.faculties = Array.isArray(faculties) ? faculties : [];
                this.users = Array.isArray(users)
                    ? users.map((user) => this.normaliseUserOption(user)).filter(Boolean)
                    : [];
                this.sortUserOptions();

                this.userDropdownEl = document.querySelector('[data-user-dropdown]');

                this.selection = new Set();
                this.sortState = { key: null, direction: 'asc' };
                this.filters = { department: 'all', job: 'all' };
                this.lastSelectionScopeKey = this.getSelectionScopeKey();

                this.tableRoot = document.querySelector('[data-table-id="facultyTable"]');
                this.tableBody = document.getElementById('facultyTableBody');
                this.selectAllEl = document.getElementById('facultySelectAll');
                this.bulkBar = document.getElementById('facultyBulkBar');
                this.selectedCountEl = document.getElementById('facultySelectedCount');

                this.alertContainer = document.getElementById('alertContainer');
                this.searchInput = document.getElementById('facultySearch');
                this.searchClear = document.getElementById('facultySearchClear');
                this.filterToggle = document.getElementById('facultyFilterToggle');
                this.filterControls = {
                    department: document.getElementById('facultyFilterDepartment'),
                    job: document.getElementById('facultyFilterJob'),
                };
                this.filterResetBtn = document.getElementById('facultyFilterReset');

                this.createModalEl = document.getElementById('facultyCreateModal');
                this.editModalEl = document.getElementById('facultyEditModal');
                this.deleteModalEl = document.getElementById('facultyDeleteModal');
                this.bulkDeleteModalEl = document.getElementById('facultyBulkDeleteModal');

                const hasBootstrap = typeof bootstrap !== 'undefined' && bootstrap && bootstrap.Modal;
                this.createModal = this.createModalEl && hasBootstrap ? new bootstrap.Modal(this.createModalEl) : null;
                this.editModal = this.editModalEl && hasBootstrap ? new bootstrap.Modal(this.editModalEl) : null;
                this.deleteModal = this.deleteModalEl && hasBootstrap ? new bootstrap.Modal(this.deleteModalEl) : null;
                this.bulkDeleteModal = this.bulkDeleteModalEl && hasBootstrap ? new bootstrap.Modal(this.bulkDeleteModalEl) : null;

                this.currentEditId = null;
                this.pendingDeleteId = null;
                this.pendingBulkIds = null;

                this.controller = null;

                this.bindBaseEvents();
                this.initSorting();
                this.initSearchInput();
                this.initFilters();
                this.renderTable();
            }

            bindBaseEvents() {
                const createForm = document.getElementById('addFacultyForm');
                if (createForm) {
                    createForm.addEventListener('submit', (event) => {
                        event.preventDefault();
                        this.handleCreate(createForm);
                    });
                    if (this.createModalEl) {
                        this.createModalEl.addEventListener('hidden.bs.modal', () => {
                            createForm.reset();
                        });
                    }
                }

                const editForm = document.getElementById('editFacultyForm');
                if (editForm) {
                    editForm.addEventListener('submit', (event) => {
                        event.preventDefault();
                        this.handleUpdate(editForm);
                    });
                }

                // Bind the submit event for the import form to show loading state on the submit button
                const importForm = document.getElementById('facultyImportForm');
                if (importForm) {
                    importForm.addEventListener('submit', () => {
                        const submitBtn = importForm.querySelector('[type="submit"]');
                        this.toggleButtonLoading(submitBtn, true, 'Import', 'Importing...');
                    });
                }

                const confirmDeleteBtn = document.getElementById('confirmFacultyDeleteBtn');
                if (confirmDeleteBtn) {
                    confirmDeleteBtn.addEventListener('click', () => {
                        if (this.pendingDeleteId !== null) {
                            this.handleDelete(this.pendingDeleteId);
                        }
                    });
                }

                const confirmBulkBtn = document.getElementById('confirmFacultyBulkDeleteBtn');
                if (confirmBulkBtn) {
                    confirmBulkBtn.addEventListener('click', () => {
                        if (Array.isArray(this.pendingBulkIds) && this.pendingBulkIds.length > 0) {
                            this.executeBulkDelete(this.pendingBulkIds.slice());
                        }
                    });
                }

                if (this.selectAllEl) {
                    this.selectAllEl.addEventListener('change', (event) => {
                        this.handleSelectAll(event.target.checked);
                    });
                }

                if (this.bulkBar) {
                    this.bulkBar.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-bulk-action]');
                        if (!button) {
                            return;
                        }

                        if (button.dataset.bulkAction === 'clear') {
                            this.clearSelection();
                        } else if (button.dataset.bulkAction === 'delete') {
                            this.handleBulkDeletePrompt();
                        }
                    });
                }

                if (this.tableRoot) {
                    this.tableRoot.addEventListener('table:updated', () => {
                        const scopeKey = this.getSelectionScopeKey();
                        if (this.selectAllEl?.checked && scopeKey !== this.lastSelectionScopeKey) {
                            this.lastSelectionScopeKey = scopeKey;
                            this.clearSelection();
                        } else {
                            this.lastSelectionScopeKey = scopeKey;
                        }
                        this.syncSelectAllState();
                        this.updateBulkBar();
                        this.refreshPillPalettes();
                    });
                }
            }

            initSorting() {
                this.sortHeaders = Array.from(document.querySelectorAll('#facultyTable thead th[data-sort-key]'));
                this.sortHeaders.forEach((header) => {
                    header.dataset.sortState = 'none';
                    header.addEventListener('click', () => {
                        const sortKey = header.dataset.sortKey;
                        if (!sortKey) {
                            return;
                        }

                        if (this.sortState.key === sortKey) {
                            this.sortState.direction = this.sortState.direction === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.sortState.key = sortKey;
                            this.sortState.direction = 'asc';
                        }

                        this.updateSortIndicators();
                        this.renderTable();
                    });
                });
                this.updateSortIndicators();
            }

            initSearchInput() {
                if (!this.searchInput || !this.searchClear) {
                    return;
                }

                const toggleClear = () => {
                    if (this.searchInput.value.trim() === '') {
                        this.searchClear.classList.remove('is-visible');
                    } else {
                        this.searchClear.classList.add('is-visible');
                    }
                };

                this.searchInput.addEventListener('input', toggleClear);
                this.searchClear.addEventListener('click', () => {
                    this.searchInput.value = '';
                    toggleClear();
                    this.searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                });

                toggleClear();
            }

            updateSortIndicators() {
                if (!Array.isArray(this.sortHeaders)) {
                    return;
                }

                this.sortHeaders.forEach((header) => {
                    header.classList.remove('sorted-asc', 'sorted-desc');
                    header.dataset.sortState = 'none';

                    if (header.dataset.sortKey === this.sortState.key) {
                        const direction = this.sortState.direction === 'asc' ? 'sorted-asc' : 'sorted-desc';
                        header.classList.add(direction);
                        header.dataset.sortState = this.sortState.direction;
                    }
                });
            }

            renderTable() {
                if (!this.tableBody) {
                    return;
                }

                const data = this.getFilteredFaculties();
                const availableIds = new Set(data.map((faculty) => String(faculty.id)));
                Array.from(this.selection).forEach((id) => {
                    if (!availableIds.has(String(id))) {
                        this.selection.delete(String(id));
                    }
                });

                const tableHtml = data.length === 0
                    ? this.buildEmptyStateRow()
                    : data.map((faculty) => this.buildRowHTML(faculty)).join('');

                this.tableBody.innerHTML = tableHtml + this.buildSearchEmptyRow();

                this.attachRowEventListeners();
                this.attachRowSelectionHandlers();
                this.syncSelectAllState();
                this.updateBulkBar();
                this.refreshPillPalettes();

                if (this.controller) {
                    this.controller.refresh();
                } else if (this.tableRoot && window.TableController) {
                    this.controller = new TableController(this.tableRoot);
                    window.tableControllers = window.tableControllers || {};
                    window.tableControllers.facultyTable = this.controller;
                }
                this.updateFilterToggleState();
            }

            refreshPillPalettes() {
                if (!this.tableRoot) {
                    return;
                }
                applyPillPalettes(this.tableRoot);
            }

            getFilteredFaculties() {
                const data = this.getSortedFaculties();
                return data.filter((faculty) => this.matchesFilters(faculty));
            }

            getSortedFaculties() {
                const data = Array.isArray(this.faculties) ? [...this.faculties] : [];
                if (!this.sortState.key) {
                    const getIdValue = (item) => {
                        if (!item || typeof item.id === 'undefined' || item.id === null) {
                            return 0;
                        }
                        return item.id;
                    };
                    return data.sort((a, b) => Number(getIdValue(b)) - Number(getIdValue(a)));
                }

                const multiplier = this.sortState.direction === 'asc' ? 1 : -1;
                return data.sort((a, b) => {
                    const valueA = this.getSortValue(a, this.sortState.key);
                    const valueB = this.getSortValue(b, this.sortState.key);

                    return String(valueA ?? '').localeCompare(String(valueB ?? ''), undefined, { sensitivity: 'base' }) * multiplier;
                });
            }

            initFilters() {
                this.refreshFilterOptions();

                Object.entries(this.filterControls).forEach(([key, select]) => {
                    if (!select) return;
                    select.addEventListener('change', (event) => {
                        this.filters[key] = event.target.value || 'all';
                        this.renderTable();
                    });
                });

                if (this.filterResetBtn) {
                    this.filterResetBtn.addEventListener('click', () => {
                        Object.keys(this.filters).forEach((key) => {
                            this.filters[key] = 'all';
                            if (this.filterControls[key]) {
                                this.filterControls[key].value = 'all';
                            }
                        });
                        this.renderTable();
                    });
                }
            }

            refreshFilterOptions() {
                const departmentOptions = this.getUniqueValues(this.faculties, (faculty) => {
                    if (!faculty || typeof faculty !== 'object') {
                        return [];
                    }
                    return this.parseDepartments(faculty.department);
                });
                const jobOptions = this.getUniqueValues(this.faculties, (faculty) => {
                    if (!faculty || typeof faculty !== 'object') {
                        return '';
                    }
                    if (faculty.job_title) {
                        return faculty.job_title;
                    }
                    return faculty.user && faculty.user.job_title ? faculty.user.job_title : '';
                });

                this.populateFilterSelect(this.filterControls.department, departmentOptions);
                this.populateFilterSelect(this.filterControls.job, jobOptions);
                this.updateFilterToggleState();
            }

            getUniqueValues(items, accessor) {
                const seen = new Map();
                (items || []).forEach((item) => {
                    const raw = accessor(item);
                    if (Array.isArray(raw)) {
                        raw.forEach((value) => {
                            const normalised = this.normaliseValue(value);
                            if (!normalised || seen.has(normalised)) {
                                return;
                            }
                            seen.set(normalised, this.formatDisplayText(value));
                        });
                        return;
                    }
                    const value = raw ?? '';
                    const normalised = this.normaliseValue(value);
                    if (!normalised || seen.has(normalised)) {
                        return;
                    }
                    seen.set(normalised, this.formatDisplayText(value));
                });
                return Array.from(seen.entries())
                    .sort((a, b) => a[1].localeCompare(b[1]))
                    .map(([value, label]) => ({ value, label }));
            }

            populateFilterSelect(select, options) {
                if (!select) return;
                const previous = select.value;
                const opts = ['<option value="all">All</option>']
                    .concat(options.map((option) => `<option value="${option.value}">${option.label}</option>`));
                select.innerHTML = opts.join('');
                select.value = options.some((option) => option.value === previous) ? previous : 'all';
            }

            matchesFilters(faculty) {
                if (!faculty) {
                    return false;
                }
                const facultyUser = faculty && faculty.user ? faculty.user : null;
                if (this.filters.department !== 'all') {
                    const departments = this.parseDepartments(faculty && faculty.department ? faculty.department : '');
                    const normalizedDepartments = departments.map((value) => this.normaliseValue(value));
                    if (!normalizedDepartments.includes(this.filters.department)) {
                        return false;
                    }
                }
                if (this.filters.job !== 'all') {
                    let jobValue = '';
                    if (faculty && faculty.job_title) {
                        jobValue = faculty.job_title;
                    } else if (facultyUser && facultyUser.job_title) {
                        jobValue = facultyUser.job_title;
                    }
                    const job = this.normaliseValue(jobValue);
                    if (job !== this.filters.job) {
                        return false;
                    }
                }
                return true;
            }

            updateFilterToggleState() {
                if (!this.filterToggle) return;
                const isActive = Object.values(this.filters).some((value) => value !== 'all');
                this.filterToggle.classList.toggle('is-active', isActive);
            }

            getSortValue(faculty, key) {
                const facultyUser = faculty && faculty.user ? faculty.user : null;
                switch (key) {
                    case 'name':
                        return facultyUser && facultyUser.name ? facultyUser.name : '';
                    case 'employee':
                        return faculty && faculty.employee_no ? faculty.employee_no : '';
                    case 'department':
                        return faculty && faculty.department ? faculty.department : '';
                    case 'job':
                        if (faculty && faculty.job_title) {
                            return faculty.job_title;
                        }
                        return facultyUser && facultyUser.job_title ? facultyUser.job_title : '';
                    default:
                        return '';
                }
            }

            buildEmptyStateRow() {
                return `
                    <tr data-empty>
                        <td colspan="<?php echo e(($canDelete || $showDeleteDisabled) ? 6 : 5); ?>" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-user-group display-4 text-muted mb-3"></i>
                                <h5 class="mb-2">No faculty members found</h5>
                                <p class="text-muted mb-0">Add your first faculty member using the actions above.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            buildSearchEmptyRow() {
                return `
                    <tr data-empty-search style="display: none;">
                        <td colspan="<?php echo e(($canDelete || $showDeleteDisabled) ? 6 : 5); ?>" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-magnifying-glass display-4 text-muted mb-3"></i>
                                <h5 class="mb-2">No results found</h5>
                                <p class="text-muted mb-0">Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            buildRowHTML(faculty) {
                const rowId = String(faculty.id);
                const isSelected = this.selection.has(rowId);

                const facultyUser = faculty && faculty.user ? faculty.user : null;
                const rawName = facultyUser && facultyUser.name ? facultyUser.name : 'N/A';
                const displayName = this.formatDisplayText(rawName);
                const emailRaw = facultyUser && facultyUser.email ? facultyUser.email : '';
                const email = this.escapeHtml(emailRaw);
                const employeeNo = this.escapeHtml(faculty && faculty.employee_no ? faculty.employee_no : 'N/A');
                const departmentList = this.parseDepartments(faculty && faculty.department ? faculty.department : '')
                    .map((value) => this.formatDisplayText(value))
                    .filter((value) => value !== '');
                if (departmentList.length === 0) {
                    departmentList.push('N/A');
                }
                let rawJobTitle = 'N/A';
                if (faculty && faculty.job_title) {
                    rawJobTitle = faculty.job_title;
                } else if (facultyUser && facultyUser.job_title) {
                    rawJobTitle = facultyUser.job_title;
                }
                const jobTitle = this.formatDisplayText(rawJobTitle);
                const nameCellTitle = [displayName, emailRaw].filter(Boolean).join(' • ');
                const employeeAttr = 'employee';
                const jobTitleAttr = this.escapeAttribute(jobTitle);

                const searchTerms = [
                    rawName,
                    employeeNo,
                    faculty && faculty.department ? faculty.department : '',
                    faculty && faculty.job_title ? faculty.job_title : '',
                    facultyUser && facultyUser.job_title ? facultyUser.job_title : '',
                    email,
                ].join(' ').toLowerCase();

                const selectionCell = (facultyPermissions.canDelete || facultyPermissions.showDeleteDisabled)
                    ? `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input evaluation-checkbox" data-row-select value="${rowId}" ${isSelected ? 'checked' : ''}>
                        </td>
                    `
                    : '';

                const actionItems = `
                    ${facultyPermissions.canEdit ? `
                        <li>
                            <button type="button" class="dropdown-item" data-action="edit" data-id="${rowId}">Edit</button>
                        </li>
                    ` : ''}
                    ${facultyPermissions.canEdit && (facultyPermissions.canDelete || facultyPermissions.showDeleteDisabled) ? '<li><hr class="dropdown-divider"></li>' : ''}
                    ${facultyPermissions.canDelete ? `
                        <li>
                            <button type="button" class="dropdown-item text-danger" data-action="delete" data-id="${rowId}">Delete</button>
                        </li>
                    ` : ''}
                    ${(!facultyPermissions.canDelete && facultyPermissions.showDeleteDisabled) ? `
                        <li>
                            <button type="button" class="dropdown-item disabled text-muted" disabled aria-disabled="true">Delete</button>
                        </li>
                    ` : ''}
                `;

                const actionsCell = (facultyPermissions.canEdit || facultyPermissions.canDelete || facultyPermissions.showDeleteDisabled)
                    ? `
                        <td class="actions-cell">
                            <div class="dropdown evaluation-actions">
                                <button class="evaluation-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    ${actionItems}
                                </ul>
                            </div>
                        </td>
                    `
                    : '<td class="text-muted">—</td>';

                return `
                    <tr data-faculty-id="${rowId}" class="${isSelected ? 'is-selected' : ''}" data-search="${this.escapeAttribute(searchTerms)}">
                        ${selectionCell}
                        <td>
                            <div class="table-cell-stack is-wide" title="${this.escapeAttribute(nameCellTitle)}">
                                <span class="faculty-name text-dark d-block">${this.escapeHtml(displayName)}</span>
                                ${email ? `<small class="text-muted">${email}</small>` : ''}
                            </div>
                        </td>
                        <td class="evaluation-pill-cell">
                            <span class="evaluation-pill"
                                  data-pill-palette="purple"
                                  data-pill-value="${employeeAttr}">${employeeNo}</span>
                        </td>
                        <td class="evaluation-pill-cell">
                            <div class="evaluation-pill-group">
                                ${departmentList.map((department) => {
                                    const departmentAttr = this.escapeAttribute(department);
                                    return `
                                        <span class="evaluation-pill"
                                              data-pill-palette="blue"
                                              data-pill-value="${departmentAttr}">${this.escapeHtml(department)}</span>
                                    `;
                                }).join('')}
                            </div>
                        </td>
                        <td>
                            <span class="table-text-truncate is-wide" title="${jobTitleAttr}">${this.escapeHtml(jobTitle)}</span>
                        </td>
                        ${actionsCell}
                    </tr>
                `;
            }

            attachRowEventListeners() {
                if (!this.tableBody) {
                    return;
                }

                this.tableBody.querySelectorAll('[data-action="edit"]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const id = Number(button.dataset.id);
                        if (!Number.isNaN(id)) {
                            this.openEditModal(id);
                        }
                    });
                });

                this.tableBody.querySelectorAll('[data-action="delete"]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const id = Number(button.dataset.id);
                        if (!Number.isNaN(id)) {
                            this.openDeleteModal(id);
                        }
                    });
                });
            }

            attachRowSelectionHandlers() {
                if (!this.tableBody) {
                    return;
                }

                const checkboxes = this.tableBody.querySelectorAll('[data-row-select]');
                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        const id = checkbox.value;
                        const row = checkbox.closest('tr');
                        if (!row) {
                            return;
                        }

                        if (checkbox.checked) {
                            this.selection.add(String(id));
                        } else {
                            this.selection.delete(String(id));
                        }

                        row.classList.toggle('is-selected', checkbox.checked);
                        this.syncSelectAllState();
                        this.updateBulkBar();
                    });
                });
            }

            getSelectableRows() {
                if (this.controller && Array.isArray(this.controller.filteredRows)) {
                    return this.controller.filteredRows;
                }
                if (!this.tableBody) {
                    return [];
                }
                return Array.from(this.tableBody.querySelectorAll('tr[data-faculty-id]'));
            }

            getSelectionScopeKey() {
                const searchTerm = this.controller?.searchTerm ?? '';
                const filterKey = JSON.stringify(this.filters);
                return `${searchTerm}|${filterKey}`;
            }

            handleSelectAll(shouldSelect) {
                const rows = this.getSelectableRows();
                rows.forEach((row) => {
                    const checkbox = row.querySelector('[data-row-select]');
                    if (!checkbox) {
                        return;
                    }
                    checkbox.checked = shouldSelect;
                    if (shouldSelect) {
                        this.selection.add(String(checkbox.value));
                    } else {
                        this.selection.delete(String(checkbox.value));
                    }
                    row.classList.toggle('is-selected', shouldSelect);
                });

                this.syncSelectAllState();
                this.updateBulkBar();
            }

            handleCreate(form) {
                const data = this.extractFormData(form);
                if (!this.validateForm(data)) {
                    return;
                }

                const submitBtn = form.querySelector('[type="submit"]');
                this.toggleButtonLoading(submitBtn, true, 'Save', 'Saving...');

                fetch('<?php echo e(route('dm.faculties.store')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                    .then(async (response) => {
                        const payload = await response.json();
                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Failed to add faculty member.');
                        }
                        return payload;
                    })
                    .then((payload) => {
                        if (Array.isArray(this.faculties)) {
                            this.faculties.push(payload.data);
                        } else {
                            this.faculties = [payload.data];
                        }
                        this.renderTable();
                        this.refreshFilterOptions();
                        this.removeUserOption(payload.data && payload.data.user_id ? payload.data.user_id : data.user_id);
                        if (this.createModal) {
                            this.createModal.hide();
                        }
                        form.reset();
                        this.showAlert('success', payload.message || 'Faculty member added successfully.');
                    })
                    .catch((error) => {
                        this.showAlert('error', error.message || 'Failed to add faculty member.');
                    })
                    .finally(() => {
                        this.toggleButtonLoading(submitBtn, false, 'Save');
                    });
            }

            handleUpdate(form) {
                if (this.currentEditId === null) {
                    return;
                }

                const data = this.extractFormData(form);
                if (!this.validateForm(data)) {
                    return;
                }

                const submitBtn = form.querySelector('[type="submit"]');
                this.toggleButtonLoading(submitBtn, true, 'Save Changes', 'Saving...');

                fetch(`<?php echo e(route('dm.faculties.update', ':id')); ?>`.replace(':id', this.currentEditId), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                    .then(async (response) => {
                        const payload = await response.json();
                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Failed to update faculty member.');
                        }
                        return payload;
                    })
                    .then((payload) => {
                        const index = this.faculties.findIndex((item) => item.id === this.currentEditId);
                        if (index !== -1) {
                            this.faculties[index] = payload.data;
                        }
                        this.renderTable();
                        this.refreshFilterOptions();
                        if (this.editModal) {
                            this.editModal.hide();
                        }
                        this.showAlert('success', payload.message || 'Faculty member updated successfully.');
                    })
                    .catch((error) => {
                        this.showAlert('error', error.message || 'Failed to update faculty member.');
                    })
                    .finally(() => {
                        this.toggleButtonLoading(submitBtn, false, 'Save Changes');
                    });
            }

            handleDelete(facultyId) {
                const targetFaculty = this.faculties.find((item) => Number(item.id) === Number(facultyId));
                const confirmBtn = document.getElementById('confirmFacultyDeleteBtn');
                this.toggleButtonLoading(confirmBtn, true, 'Delete', 'Deleting...');

                fetch(`<?php echo e(route('dm.faculties.destroy', ':id')); ?>`.replace(':id', facultyId), {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(async (response) => {
                        const payload = await response.json();
                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Failed to delete faculty member.');
                        }
                        return payload;
                    })
                    .then((payload) => {
                        this.faculties = this.faculties.filter((faculty) => faculty.id !== facultyId);
                        this.selection.delete(String(facultyId));
                        this.renderTable();
                        this.refreshFilterOptions();
                        this.restoreUserOptionFromFaculty(targetFaculty);
                        if (this.deleteModal) {
                            this.deleteModal.hide();
                        }
                        this.showAlert('success', payload.message || 'Faculty member deleted successfully.');
                    })
                    .catch((error) => {
                        this.showAlert('error', error.message || 'Failed to delete faculty member.');
                    })
                    .finally(() => {
                        this.toggleButtonLoading(confirmBtn, false, 'Delete');
                        this.pendingDeleteId = null;
                    });
            }

            handleBulkDeletePrompt() {
                if (this.selection.size === 0) {
                    return;
                }

                const ids = Array.from(this.selection)
                    .map((id) => Number(id))
                    .filter((id) => !Number.isNaN(id));

                if (!ids.length) {
                    return;
                }

                this.pendingBulkIds = ids;

                const countEl = document.getElementById('facultyBulkDeleteCount');
                if (countEl) {
                    countEl.textContent = ids.length;
                }

                if (this.bulkDeleteModal) {
                    this.bulkDeleteModal.show();
                }
            }

            executeBulkDelete(ids) {
                const targetIds = Array.isArray(ids)
                    ? ids.map((id) => Number(id)).filter((id) => !Number.isNaN(id))
                    : [];
                const confirmBtn = document.getElementById('confirmFacultyBulkDeleteBtn');
                this.toggleButtonLoading(confirmBtn, true, 'Delete Selected', 'Deleting...');

                fetch('<?php echo e(route('dm.faculties.bulk-destroy')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids })
                })
                    .then(async (response) => {
                        const payload = await response.json();
                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Failed to delete selected faculty members.');
                        }
                        return payload;
                    })
                    .then((payload) => {
                        const idSet = new Set(targetIds);
                        const removedFaculties = this.faculties.filter((faculty) => idSet.has(Number(faculty.id)));
                        this.faculties = this.faculties.filter((faculty) => !idSet.has(Number(faculty.id)));
                        ids.forEach((id) => this.selection.delete(String(id)));
                        this.renderTable();
                        this.refreshFilterOptions();
                        removedFaculties.forEach((faculty) => this.restoreUserOptionFromFaculty(faculty));
                        if (this.bulkDeleteModal) {
                            this.bulkDeleteModal.hide();
                        }
                        this.showAlert('success', payload.message || 'Selected faculty members deleted successfully.');
                    })
                    .catch((error) => {
                        this.showAlert('error', error.message || 'Failed to delete selected faculty members.');
                    })
                    .finally(() => {
                        this.toggleButtonLoading(confirmBtn, false, 'Delete Selected');
                        this.pendingBulkIds = null;
                    });
            }

            openEditModal(facultyId) {
                const faculty = this.faculties.find((item) => item.id === facultyId);
                if (!faculty) {
                    this.showAlert('error', 'Selected faculty record was not found.');
                    return;
                }

                this.currentEditId = facultyId;
                const facultyUser = faculty && faculty.user ? faculty.user : null;
                const form = document.getElementById('editFacultyForm');
                if (!form) {
                    return;
                }

                form.querySelector('[name="faculty_id"]').value = faculty.id;
                form.querySelector('[name="user_id"]').value = faculty.user_id ?? '';
                form.querySelector('[name="employee_no"]').value = faculty.employee_no ?? '';
                const departmentValue = this.formatDisplayText(faculty.department ?? '');
                const departmentMultiselect = form.querySelector('[data-department-multiselect]');
                if (departmentMultiselect?.departmentMultiselectApi) {
                    departmentMultiselect.departmentMultiselectApi.setSelected(departmentValue);
                } else {
                    form.querySelector('[name="department"]').value = departmentValue;
                }
                let editJobValue = '';
                if (faculty.job_title) {
                    editJobValue = faculty.job_title;
                } else if (facultyUser && facultyUser.job_title) {
                    editJobValue = facultyUser.job_title;
                }
                form.querySelector('[name="job_title"]').value = this.formatDisplayText(editJobValue);

                const nameInput = document.getElementById('editFacultyName');
                if (nameInput) {
                    const editName = facultyUser && facultyUser.name ? facultyUser.name : '';
                    nameInput.value = this.formatDisplayText(editName);
                }

                if (this.editModal) {
                    this.editModal.show();
                }
            }

            openDeleteModal(facultyId) {
                const faculty = this.faculties.find((item) => item.id === facultyId);
                if (!faculty) {
                    this.showAlert('error', 'Selected faculty record was not found.');
                    return;
                }

                this.pendingDeleteId = facultyId;
                const facultyUser = faculty && faculty.user ? faculty.user : null;

                const nameEl = document.getElementById('facultyDeleteName');
                if (nameEl) {
                    const deleteName = facultyUser && facultyUser.name ? facultyUser.name : 'this faculty member';
                    nameEl.textContent = this.formatDisplayText(deleteName);
                }

                if (this.deleteModal) {
                    this.deleteModal.show();
                }
            }

            clearSelection() {
                this.selection.clear();

                if (this.tableBody) {
                    this.tableBody.querySelectorAll('[data-row-select]').forEach((checkbox) => {
                        checkbox.checked = false;
                    });

                    this.tableBody.querySelectorAll('tr').forEach((row) => {
                        row.classList.remove('is-selected');
                    });
                }

                if (this.selectAllEl) {
                    this.selectAllEl.checked = false;
                    this.selectAllEl.indeterminate = false;
                }

                this.updateBulkBar();
            }

            syncSelectAllState() {
                if (!this.selectAllEl) {
                    return;
                }

                const rows = this.getSelectableRows();
                if (rows.length === 0) {
                    this.selectAllEl.checked = false;
                    this.selectAllEl.indeterminate = false;
                    return;
                }

                const selectedVisible = rows.filter((row) => this.selection.has(row.dataset.facultyId)).length;

                if (selectedVisible === 0) {
                    this.selectAllEl.checked = false;
                    this.selectAllEl.indeterminate = false;
                } else if (selectedVisible === rows.length) {
                    this.selectAllEl.checked = true;
                    this.selectAllEl.indeterminate = false;
                } else {
                    this.selectAllEl.checked = false;
                    this.selectAllEl.indeterminate = true;
                }
            }

            updateBulkBar() {
                if (!this.bulkBar || !this.selectedCountEl) {
                    return;
                }

                const count = this.selection.size;
                this.selectedCountEl.textContent = `${count} Selected`;
                this.bulkBar.classList.toggle('d-none', count === 0);
            }

            extractFormData(form) {
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                return data;
            }

            validateForm(data) {
                const required = ['employee_no', 'department', 'job_title'];
                const missing = required.filter((field) => !data[field] || String(data[field]).trim() === '');
                const hasName = data.user_name && String(data.user_name).trim() !== '';
                const hasUserId = data.user_id && String(data.user_id).trim() !== '';

                if (!hasName && !hasUserId) {
                    this.showAlert('error', 'Please enter a faculty name or select a registered user.');
                    return false;
                }

                if (missing.length > 0) {
                    this.showAlert('error', 'Please complete all required fields.');
                    return false;
                }

                return true;
            }

            toggleButtonLoading(button, isLoading, defaultText = null, loadingText = null) {
                if (!button) {
                    return;
                }

                const idleText = defaultText ?? button.dataset.defaultText ?? button.textContent.trim();
                const busyText = loadingText ?? button.dataset.loadingText ?? 'Saving...';

                if (isLoading) {
                    button.dataset.defaultText = idleText;
                    button.dataset.loadingText = busyText;
                    button.disabled = true;
                    button.textContent = busyText;
                } else {
                    const original = button.dataset.defaultText || idleText;
                    button.disabled = false;
                    button.textContent = original;
                }
            }

            showAlert(type, message) {
                if (window.dmToast && typeof window.dmToast.show === 'function') {
                    const toastType = type === 'error' ? 'danger' : type;
                    window.dmToast.show({ type: toastType, message });
                    return;
                }

                if (!this.alertContainer) {
                    return;
                }

                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                this.alertContainer.innerHTML = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${this.escapeHtml(message)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }

            escapeHtml(value) {
                if (value === null || value === undefined) {
                    return '';
                }

                return String(value).replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                }[char] || char));
            }

            escapeAttribute(value) {
                return this.escapeHtml(value).replace(/"/g, '&quot;');
            }

            formatDisplayText(value) {
                if (value === null || value === undefined) {
                    return '';
                }

                return String(value);
            }

            parseDepartments(value) {
                if (value === null || value === undefined) {
                    return [];
                }
                const text = String(value).trim();
                if (text === '') {
                    return [];
                }
                return text
                    .split(',')
                    .map((item) => item.trim())
                    .filter((item) => item !== '');
            }

            normaliseValue(value) {
                return String(value ?? '')
                    .trim()
                    .toLowerCase();
            }

            normaliseUserOption(user) {
                if (!user || typeof user !== 'object') {
                    return null;
                }
                const id = Number(user.id);
                if (Number.isNaN(id)) {
                    return null;
                }
                const rawName = user.name ?? user.displayName ?? '';
                const displayName = this.formatDisplayText(rawName) || 'Unknown';
                const email = user.email ?? '';
                return {
                    id,
                    displayName,
                    email,
                    searchName: String(rawName ?? '').toLowerCase(),
                    searchEmail: String(email ?? '').toLowerCase(),
                };
            }

            sortUserOptions() {
                if (!Array.isArray(this.users)) {
                    this.users = [];
                    return;
                }
                this.users.sort((a, b) => {
                    const nameA = (a?.displayName || '').toLowerCase();
                    const nameB = (b?.displayName || '').toLowerCase();
                    return nameA.localeCompare(nameB);
                });
            }

            buildUserOptionHTML(option) {
                if (!option) {
                    return '';
                }
                const id = this.escapeAttribute(String(option.id ?? ''));
                const nameAttr = this.escapeAttribute(option.displayName || 'Unknown');
                const nameDisplay = this.escapeHtml(option.displayName || 'Unknown');
                const searchName = this.escapeAttribute(option.searchName || '');
                const searchEmail = this.escapeAttribute(option.searchEmail || '');
                const emailMarkup = option.email ? `<small>${this.escapeHtml(option.email)}</small>` : '';

                return `
                    <button type="button"
                            class="dropdown-item"
                            data-user-option
                            data-user-id="${id}"
                            data-user-name="${nameAttr}"
                            data-user-name-lower="${searchName}"
                            data-user-email-lower="${searchEmail}">
                        <span>${nameDisplay}</span>
                        ${emailMarkup}
                    </button>
                `;
            }

            refreshUserDropdownOptions({ preserveSelection = false } = {}) {
                if (!this.userDropdownEl) {
                    return;
                }

                const list = this.userDropdownEl.querySelector('[data-user-list]');
                if (!list) {
                    return;
                }

                if (!this.users.length) {
                    list.innerHTML = '<div class="text-muted small px-2 py-1">No available users</div>';
                } else {
                    list.innerHTML = this.users.map((option) => this.buildUserOptionHTML(option)).join('');
                }

                const controller = this.userDropdownEl.__userDropdown;
                controller?.applyFilter?.();

                const hiddenInput = this.userDropdownEl.querySelector('input[name="user_id"]');
                if (!hiddenInput) {
                    return;
                }

                if (preserveSelection && hiddenInput.value) {
                    const selectedOption = Array.from(this.userDropdownEl.querySelectorAll('[data-user-option]'))
                        .find((option) => option.dataset.userId === hiddenInput.value);
                    if (selectedOption) {
                        controller?.setName?.(selectedOption.dataset.userName || '');
                        return;
                    }
                }

                hiddenInput.value = '';
                controller?.resetSelection?.();
            }

            removeUserOption(userId) {
                if (userId === null || userId === undefined || !Array.isArray(this.users) || !this.users.length) {
                    return;
                }
                const targetId = Number(userId);
                if (Number.isNaN(targetId)) {
                    return;
                }
                const originalLength = this.users.length;
                this.users = this.users.filter((option) => Number(option.id) !== targetId);
                if (this.users.length !== originalLength) {
                    this.refreshUserDropdownOptions();
                }
            }

            restoreUserOptionFromFaculty(faculty) {
                if (!faculty) {
                    return;
                }
                const baseUser = faculty.user
                    ? faculty.user
                    : {
                        id: faculty.user_id,
                        name: faculty.name || 'Unknown',
                        email: faculty.email || '',
                    };
                const option = this.normaliseUserOption(baseUser);
                if (!option) {
                    return;
                }
                const exists = this.users.some((user) => Number(user.id) === Number(option.id));
                if (exists) {
                    return;
                }
                this.users.push(option);
                this.sortUserOptions();
                this.refreshUserDropdownOptions({ preserveSelection: true });
            }

        }
    </script>
<?php $__env->stopSection(); ?>

<!-- Faculty Management Blade View Frontend -->
<?php $__env->startSection('content'); ?> 
    <!-- Faculty Management Content -->
    <?php
        $flashSuccess = session('success');
        $flashError = session('error');
        $pageToasts = [];
        
        if ($flashSuccess) {
            $pageToasts[] = ['type' => 'success', 'message' => $flashSuccess];
        }
        
        if ($flashError) {
            $pageToasts[] = ['type' => 'danger', 'message' => $flashError];
        }
    ?>

    <?php echo $__env->make('components.dm-toast', ['messages' => $pageToasts], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Main container for the faculty management page -->
    <div class="container-fluid">
        <div id="alertContainer" class="evaluation-alert-container"></div>

        <!-- Faculty Management Header -->
        <?php echo $__env->make('content.data-management.partials.faculties.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- Faculty Table -->
        <?php echo $__env->make('content.data-management.partials.faculties.faculty-items-table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div> <!-- End Faculty Management Container -->

    <!-- Modals rendered from partials/faculties -->
    <?php echo $__env->make('content.data-management.partials.faculties.add-faculty-form-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- Add Faculty Form Modal -->
    <?php echo $__env->make('content.data-management.partials.faculties.edit-faculty-form-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- Edit Faculty Form Modal -->
    <?php echo $__env->make('content.data-management.partials.faculties.delete-alert-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- Delete Faculty Form Modal -->
    <?php echo $__env->make('content.data-management.partials.faculties.bulk-delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- Bulk Delete Faculty Form Modal -->
    <?php echo $__env->make('content.data-management.partials.faculties.excel-import-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- Excel Import Faculty Form Modal -->
    <?php echo $__env->make('content.data-management.partials.faculties.import-all-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- Import All Modal -->

<?php $__env->stopSection(); ?> <!-- End Faculty Management Blade View Frontend -->
<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/dm-faculties.blade.php ENDPATH**/ ?>