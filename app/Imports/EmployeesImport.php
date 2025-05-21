<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToModel, WithHeadingRow
{
    protected $company_id;
    protected $department_id;
    protected $designation_id;
    protected $office_shift_id;
    protected $skippedRows = [];

    public function __construct($company_id, $department_id, $designation_id, $office_shift_id)
    {
        $this->company_id    = $company_id;
        $this->department_id = $department_id;
        $this->designation_id= $designation_id;
        $this->office_shift_id = $office_shift_id;
    }

    public function getSkippedRows(): array
    {
        return $this->skippedRows;
    }

    public function model(array $row)
    {
        // Only validate the CSV fields
        $v = Validator::make($row, [
            'firstname' => 'required|string',
            'lastname'  => 'required|string',
            'email'     => 'required|email|unique:employees,email',
            'phone'     => 'required|unique:employees,phone',
            'gender'    => 'required|in:Male,Female,Other',
        ]);

        if ($v->fails()) {
            // Track skipped rows with reason
            $this->skippedRows[] = [
                'row'    => $row,
                'reason' => $v->errors()->all(),
            ];
            return null;
        }

        return new Employee([
            'firstname'        => $row['firstname'],
            'lastname'         => $row['lastname'],
            'email'            => $row['email'],
            'phone'            => $row['phone'],
            'gender'           => $row['gender'],
            'password'         => Hash::make('12345678'),

            // 👇 Only here do we use the “outside” values
            'company_id'       => $this->company_id,
            'department_id'    => $this->department_id,
            'designation_id'   => $this->designation_id,
            'office_shift_id'  => $this->office_shift_id,

            // Optional CSV columns
            'birth_date'       => $row['birth_date']   ?? null,
            'country'          => $row['country']      ?? null,
            'city'             => $row['city']         ?? null,
            'district'         => $row['district']     ?? null,
            'state'            => $row['state']        ?? null,
            'joining_date'     => $row['joining_date'] ?? null,
            'basic_salary'     => $row['basic_salary'] ?? 0,
        ]);
    }
}