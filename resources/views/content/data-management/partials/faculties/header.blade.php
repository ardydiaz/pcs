<!-- Modern Faculty Directory Header -->
<div class="faculty-directory-hero mb-4">
    <div class="faculty-directory-hero__content">
        <div class="faculty-directory-hero__eyebrow">
            <i class="fa-solid fa-user-group"></i>
            Faculty Directory
        </div>
        <div class="faculty-directory-hero__main">
            <div>
                <h4 class="faculty-directory-hero__title mb-1">Manage Faculty Records</h4>
                <p class="faculty-directory-hero__subtitle mb-0">
                    Add, import, update, restore, and review faculty teaching loads in one place.
                </p>
            </div>
            <div class="faculty-directory-hero__actions">
                @if ($canAdd)
                    <button type="button" class="btn faculty-hero-primary evaluation-modal-trigger" data-bs-toggle="modal"
                        data-bs-target="#facultyCreateModal">
                        <i class="fa-solid fa-user-plus me-2"></i> Add Faculty
                    </button>
                @endif
                @if ($canImportAll)
                    <div class="dropdown faculty-import-dropdown">
                        <button type="button" class="btn faculty-hero-secondary dropdown-toggle" data-bs-toggle="dropdown"
                            data-bs-display="static" aria-expanded="false">
                            <i class="fa-solid fa-file-import me-2"></i> Import Tools
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end faculty-import-menu">
                            <li>
                                <button type="button" class="dropdown-item evaluation-modal-trigger" data-bs-toggle="modal"
                                    data-bs-target="#facultyConvertImportModal">
                                    <i class="fa-solid fa-file-excel me-2"></i>
                                    <span>Convert Excel Template</span>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item evaluation-modal-trigger" data-bs-toggle="modal"
                                    data-bs-target="#facultyImportAllModal">
                                    <i class="fa-solid fa-file-import me-2"></i>
                                    <span>Import Faculty and Schedule</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="faculty-directory-stats" aria-label="Faculty summary">
        <div class="faculty-directory-stat">
            <span class="faculty-directory-stat__icon"><i class="fa-solid fa-users"></i></span>
            <span class="faculty-directory-stat__label">Total Faculty</span>
            <strong class="faculty-directory-stat__value" id="facultyTotalStat">--</strong>
        </div>
        <div class="faculty-directory-stat">
            <span class="faculty-directory-stat__icon"><i class="fa-solid fa-building-columns"></i></span>
            <span class="faculty-directory-stat__label">Departments</span>
            <strong class="faculty-directory-stat__value">{{ $departmentSelectOptions->count() }}</strong>
        </div>
        <div class="faculty-directory-stat">
            <span class="faculty-directory-stat__icon"><i class="fa-solid fa-book-open-reader"></i></span>
            <span class="faculty-directory-stat__label">Showing Now</span>
            <strong class="faculty-directory-stat__value" id="facultyVisibleStat">--</strong>
        </div>
    </div>
</div>
