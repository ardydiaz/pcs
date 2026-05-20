# ImportAll CSV Format Guide

## Column Requirements

| Column | Required | Format | Validation | Example |
|--------|----------|--------|-----------|---------|
| **employeeno** | ✓ Yes | String | Min 2 characters | `E001`, `FAC123` |
| **classcode** | ✓ Yes | String | Non-empty | `CS101`, `MATH201` |
| **section** | ✓ Yes | String | Non-empty | `1`, `A`, `Section1` |
| **academicyear** | ✓ Yes | String | `YYYY` or `YYYY-YYYY` | `2024`, `2023-2024` |
| **semester** | ✓ Yes | String | `1st`, `2nd`, `Summer` or variants | `1st semester`, `2nd`, `summer` |
| **time** | ✓ Yes | String | Time range format | `07:00a - 08:30a`, `1:00p to 2:30p` |
| **day** | ✓ Yes | String | Day abbreviations | `M,W,F`, `MTWTh`, `M, T, W, TH` |
| **subjectcode** | ✗ Optional | String | Non-empty if provided | `CS`, `MATH` |
| **status** | ✗ Optional | String | Defaults to `active` | `active`, `inactive` |
| **name** | ✗ Optional* | String | Required if faculty doesn't exist | `John Doe`, `Jane Smith` |
| **department** | ✗ Optional* | String | Required if faculty doesn't exist | `Computer Science`, `Mathematics` |
| **jobtitle** | ✗ Optional* | String | Required if faculty doesn't exist | `Instructor`, `Professor` |

*Fields marked with * are only required if creating a new faculty. If faculty exists (by employeeno), these can be omitted.

---

## Valid Day Abbreviations

- **M** = Monday
- **T** = Tuesday
- **W** = Wednesday
- **TH** = Thursday
- **F** = Friday
- **S** = Saturday
- **SU** = Sunday

**Accepted Formats:**
- Comma-separated: `M,W,F` or `M, W, F`
- Continuous: `MWF` or `MTWF` or `MWFTH`
- Mixed: `M,TH,F`

---

## Valid Semester Values

**Accepts any of these inputs:**
- First semester: `1st`, `1st semester`, `1st Semester`, `FIRST_SEMESTER`
- Second semester: `2nd`, `2nd semester`, `2nd Semester`, `SECOND_SEMESTER`
- Summer: `summer`, `Summer`, `SUMMER`, `summer semester`

**Normalizes to database values:**
- `1st Semester` input → stored as `1st`
- `2nd Semester` input → stored as `2nd`
- `summer` input → stored as `Summer`

---

## Time Format Examples

**Valid formats (will be normalized to `HH:MMx - HH:MMx`):**
- `07:00a - 08:30a`
- `7:00a to 8:30a`
- `7:00AM - 8:30AM`
- `13:00 - 14:30`
- `1:00p to 2:30p`

**All normalize to:** `07:00a - 08:30a` (with extra spaces removed)

---

## Academic Year Format Examples

**Valid formats:**
- Four-digit year: `2024`, `2025`
- Year range: `2023-2024`, `2024-2025`, `2025-2026`

**Invalid formats (will be rejected):**
- `24`, `25` (must be 4 digits)
- `2023/2024` (must use `-` not `/`)
- `academic year 2024` (no extra text)

---

## Complete CSV Example

```csv
employeeno,classcode,subjectcode,section,academicyear,semester,time,day,status,name,department,jobtitle
E001,CS101,CS,1,2023-2024,1st,07:00a to 08:30a,MWF,active,John Smith,Computer Science,Instructor
E002,MATH201,MATH,A,2023-2024,2nd,01:00p - 02:30p,TTH,active,Jane Doe,Mathematics,Professor
E003,ENG102,ENG,2,2024,summer,09:00a to 10:30a,M,active,Bob Johnson,English,Instructor
E004,PHYS301,PHYS,1,2025-2026,1st semester,02:00p to 03:30p,MWF,,Sarah Williams,Physics,Professor
E005,CHEM201,CHEM,B,2024-2025,2nd,11:00a to 12:30p,TTH,active,Mike Brown,Chemistry,Lecturer
```

---

## Validation Rules & Error Messages

### Skipped if:

| Condition | Error Message |
|-----------|---------------|
| Employee number is empty or < 2 characters | `Missing or invalid employee number` |
| Class code, section, academic year, or semester missing | `Missing required course details` |
| Academic year doesn't match `YYYY` or `YYYY-YYYY` | `Invalid academic year format: [value]` |
| Time or day is empty | `Missing schedule time or day` |
| Day abbreviations invalid | `Invalid day format: [value]` |
| Faculty not found AND name/department/jobtitle missing | `Faculty with employee number not found and insufficient data to create faculty` |
| Course creation fails | `Failed to create or find course` |
| Faculty creation fails | `Failed to create or find faculty` |
| FacultyCourse creation fails | `Failed to create or find faculty course assignment` |
| FacultyCourse query fails | `Faculty course assignment query verification failed` |
| Schedule creation fails | `Failed to create or find schedule` |
| Database exception occurs | `Error: [exception message]` |

---

## Workflow Examples

### Scenario 1: New Faculty (All Fields Provided)
```csv
E999,CS999,CS,LAB1,2024-2025,1st,03:00p to 04:30p,W,active,Dr. Alice Chen,Computer Science,Senior Professor
```
**Result:** Creates new User + Faculty, then creates Course/FacultyCourse/Schedule

### Scenario 2: Existing Faculty (Only Required Fields)
```csv
E001,CS202,CS,2,2024-2025,2nd,10:00a - 11:30a,TTH
```
**Requires:** Faculty with employeeno `E001` already exists in database

### Scenario 3: Faculty with Multiple Sections (Same Faculty, Different Times)
```csv
E001,CS101,CS,1,2024-2025,1st,09:00a to 10:30a,MWF,active
E001,CS101,CS,2,2024-2025,1st,02:00p to 03:30p,TTH,active
```
**Result:** Creates two separate FacultyCourse assignments (same faculty, same course, different sections)

### Scenario 4: Same Course, Different Semesters
```csv
E002,MATH301,MATH,A,2024-2025,1st,08:00a to 09:30a,MWF,active
E002,MATH301,MATH,A,2024-2025,2nd,01:00p to 02:30p,MWF,active
```
**Result:** Faculty teaches same course in both semesters (different schedules)

---

## Tips for Clean Imports

1. **Employee Number:** Use consistent format (e.g., always `EXXXXX` or `FACXXX`)
2. **Academic Year:** Use consistent format (recommend `YYYY-YYYY` for clarity)
3. **Days:** Remove extra spaces; use either all commas or all continuous (avoid mixing)
4. **Time:** Normalize input format before export (e.g., always use `HH:MMx - HH:MMx`)
5. **New Faculty:** Provide complete info (name, department, jobtitle) to auto-create
6. **Existing Faculty:** Omit optional fields to reduce data entry errors
7. **Validation:** Check skipped records in import result for details on failures

---

## Testing Your CSV

Before bulk import:
1. Check for typos in employeeno, academicyear, semester
2. Verify day abbreviations are valid (M, T, W, TH, F, S, SU)
3. Verify time format matches `HH:MMx - HH:MMx` or `HH:MMx to HH:MMx`
4. If creating new faculty, ensure name, department, jobtitle are non-empty
5. Import small test batch first to verify format
6. Check import results for skipped records
7. Verify schedules appear in evaluation generation

---

## Troubleshooting

**"Invalid academic year format"**
- Check: Year must be 4 digits (2024, not 24)
- Check: Use `-` for range (2024-2025, not 2024/2025)

**"Invalid day format"**
- Check: Use only valid abbreviations (M, T, W, TH, F, S, SU)
- Check: Separate with commas or use continuous string (not mixed)
- Check: No lowercase letters (write `TH`, not `th`)

**"Missing or invalid employee number"**
- Check: Must be at least 2 characters
- Check: Not empty or whitespace-only

**Evaluation generation still fails?**
- Run import diagnostics: check if schedules were actually created
- Verify FacultyCourse records exist with matching faculty_id, academic_year, semester
- Check that Schedule records link to FacultyCourse via faculty_course_id
