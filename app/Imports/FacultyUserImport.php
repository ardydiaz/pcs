<?php

namespace App\Imports;

use App\Models\User; // Relationship with users using their user_id as foreign key
use App\Models\Faculty; // Relationship with faculties to create new Faculty records based on the imported data
use Maatwebsite\Excel\Concerns\ToModel; // Interface to convert each row of the Excel file into a model instance
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Interface to indicate that the first row of the Excel file contains column headings, allowing us to access row data using those headings as keys

class FacultyUserImport implements ToModel, WithHeadingRow
{
    protected $skippedRecords = []; // Array to track skipped records during import, such as those with missing required fields or duplicates
    protected $importedCount = 0; // Counter to track the number of successfully imported records, incremented each time a new faculty record is created

    // Method to retrieve the list of skipped records, which can be used for reporting or debugging purposes after the import process is complete
    public function getSkippedRecords(): array 
    {
        return $this->skippedRecords;
    }

    // Method to retrieve the count of successfully imported records, which can be used for reporting or feedback purposes after the import process is complete
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function model(array $row) // Convert each row of the Excel file into a Faculty model instance, while also creating a linked User record and handling validation and normalization of the data
    {
        //dd($row);
        // Extract and validate employee number from the row. If it's missing or empty, skip this row by returning null.
        $employeeNo = isset($row['employeeno']) ? trim((string) $row['employeeno']) : '';
        if ($employeeNo === '') {
            return null;
        }

        // Extract and validate department, name, and job title from the row. If any of these are missing or empty, skip this row by returning null.
        $department = isset($row['department']) ? trim((string) $row['department']) : '';
        $name = isset($row['name']) ? trim((string) $row['name']) : '';
        $jobTitle = isset($row['jobtitle']) ? trim((string) $row['jobtitle']) : '';

        if ($department === '' || $name === '' || $jobTitle === '') {
            return null;
        }

        // Normalize the department list to ensure consistent formatting and avoid duplicates. This will handle cases where the department data might be a comma-separated string or a JSON array.
        $normalizedDepartment = Faculty::serializeDepartmentList(
            Faculty::normalizeDepartmentList($department)
        );

        // Skip if faculty already exists with same employeeno and track the skipped record
        $existingFaculty = Faculty::where('employee_no', $employeeNo)->first();
        if ($existingFaculty) {
            $this->skippedRecords[] = $employeeNo;
            return null;
        }

        // Create linked user
        $user = User::create([
            'name' => $name,
            'department' => $normalizedDepartment,
            'job_title' => $jobTitle,
            'role' => 'Faculty',
            'status' => 'Active',
        ]);

        // Create faculty record
        $faculty = Faculty::create([
            'user_id' => $user->id,
            'employee_no' => $employeeNo,
            'department' => $normalizedDepartment,
            'job_title' => $jobTitle,
        ]);

        $this->importedCount++;
        return $faculty;
    }
}
