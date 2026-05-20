{{-- Import Schedule Modal Partial --}}
@if ($canImport)
    {{-- Import Modal --}}
    <div class="modal fade" id="scheduleImportModal" tabindex="-1" aria-labelledby="scheduleImportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered schedule-modal-dialog schedule-modal-dialog--narrow">
            <div class="modal-content schedule-card">
                <form method="POST" action="{{ route('dm.schedules.import') }}" enctype="multipart/form-data" id="scheduleImportForm">
                    @csrf
                    <div class="modal-header schedule-modal-header">
                        <h5 class="modal-title mb-0" id="scheduleImportModalLabel">Import Schedules</h5>
                        <button type="button" class="schedule-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    </div>
                    <div class="modal-body schedule-modal-body">
                        <div class="mb-0">
                            <label class="form-label" for="scheduleImportFile">Select File</label>
                            <input type="file" name="file" id="scheduleImportFile" class="form-control" accept=".csv,.txt,.xlsx" required>
                            <small class="text-muted d-block mt-2">Headers: employeeno, classcode, section, time, day, status</small>
                        </div>
                    </div>
                    <div class="modal-footer schedule-modal-footer">
                        <button type="button" class="btn btn-tertiary schedule-modal-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-schedule-primary schedule-modal-btn" data-default-text="Import" data-loading-text="Importing...">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
