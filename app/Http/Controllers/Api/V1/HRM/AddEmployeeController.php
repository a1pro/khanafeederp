<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\GmDetail;
use App\Models\Agmdetail;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\OfficeShift;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class AddEmployeeController extends Controller
{
    
    public function add_employee(Request $request)
    {
        DB::beginTransaction();
    
        try {
            $this->validate($request, [
                'firstname'      => 'required|string',
                'lastname'       => 'required|string',
                'gender'         => 'required',
                'company_id'     => 'required|integer',
                'department_id'  => 'required|integer',
                'designation_id' => 'required|integer',
                'office_shift_id'=> 'required|integer',
                'email'          => 'required|email|unique:employees,email',
            ]);
    
            $employee = new Employee();
            $employee->firstname        = $request->input('firstname');
            $employee->lastname         = $request->input('lastname');
            $employee->username         = $request->input('firstname') . ' ' . $request->input('lastname');
            $employee->phone            = $request->input('phone');
            $employee->gender           = $request->input('gender');
            $employee->birth_date       = $request->input('birth_date');
            $employee->email            = $request->input('email');
            $employee->country          = $request->input('country');
            $employee->city             = $request->input('city');
            $employee->district         = $request->input('district');
            $employee->village          = $request->input('village');
            $employee->state            = $request->input('state');
            $employee->joining_date     = $request->input('joining_date');
            $employee->company_id       = $request->input('company_id');
            $employee->department_id    = $request->input('department_id');
            $employee->designation_id   = $request->input('designation_id');
            $employee->office_shift_id  = $request->input('office_shift_id');
            $employee->basic_salary     = $request->input('basic_salary');
            $employee->password         = Hash::make('khannasales');
            $employee->save();
    
            // Generate employee code after ID is available
            $date = now()->format('d-m-y');
            switch ((int) $employee->company_id) {
                case 2:
                    $employee->emp_id = 'TF-' . $employee->id;
                    break;
                case 3:
                    $employee->emp_id = 'KF-' . $employee->id;
                    break;
                default:
                    // Optionally generate a default code or leave it null
                    $employee->emp_id = 'EMP-' . $employee->id;
                    break;
            }
            $employee->save();
    
            // Save related GM/AGM detail
            if ((int) $employee->designation_id === 1) {
                $gm = new Agmdetail();
                $gm->name           = $employee->firstname . ' ' . $employee->lastname;
                $gm->phone          = $employee->phone;
                $gm->gender         = $employee->gender;
                $gm->email          = $employee->email;
                $gm->state          = $employee->state;
                $gm->district       = $employee->district;
                $gm->company_id     = $employee->company_id;
                $gm->department_id  = $employee->department_id;
                $gm->save();
            } elseif ((int) $employee->designation_id === 2) {
                $agm = new GmDetail();
                $agm->name          = $employee->firstname . ' ' . $employee->lastname;
                $agm->phone         = $employee->phone;
                $agm->gender        = $employee->gender;
                $agm->email         = $employee->email;
                $agm->state         = $employee->state;
                $agm->district      = $employee->district;
                $agm->company_id    = $employee->company_id;
                $agm->department_id = $employee->department_id;
                $agm->save();
            }
    
            DB::commit();
    
            return response()->json([
                'success'     => true,
                'status_code' => 200,
                'message'     => 'Employee added successfully',
                'data'        => $employee,
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success'     => false,
                'status_code' => 500,
                'message'     => 'Something went wrong: ' . $e->getMessage(),
            ]);
        }
    }
    

// ====================================Company Get==================================

   public function company()
   {
        DB::beginTransaction();

        try{

            $company = Company::select('id','name')->get();

             DB::commit();
            return response()->json([
                'success'           =>      true,
                'status_code'       =>      200,
                'message'           =>      'Company Data Fetch Succesfully',
                'data'              =>      $company
            ]);

           
        }
        catch(\Exception $e){
            DB::rollback();

            return response()->json([
                'success'       =>    false,
                'status_code'   =>      500,
                'message'       =>    'Something Went Wrong'  .$e->getMessage()
            ]);
        }
   } 


// =================================Department==================================
 public function department(Request $request)
{
    // Validate that company_id is present and is an integer
    $request->validate([
        'company_id' => 'required|integer|exists:companies,id'
    ]);

    DB::beginTransaction();

    try {
        $companyId = $request->company_id;

        $departments = Department::select('id', 'department')
            ->where('company_id', $companyId)
            ->get();

        DB::commit();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Departments retrieved successfully.',
            'data' => $departments
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}


// ====================================Designation===============================
public function designation(Request $request)
{
    // Validate that company_id is present and is an integer
    $request->validate([
        'company_id' => 'required|integer|exists:companies,id',
        'department_id' => 'required'
    ]);

    DB::beginTransaction();

    try {
        $companyId = $request->company_id;
        $departmentId = $request->department_id;
        $departments = Designation::select('id', 'designation')
            ->where('company_id', $companyId)->where('department_id',$departmentId)
            ->get();

        DB::commit();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Designation retrieved successfully.',
            'data' => $departments
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}

// ==================================Office Shift===============================

public function officeShift(Request $request)
{
    // Validate that company_id is present and is an integer
    $request->validate([
        'company_id' => 'required|integer|exists:companies,id'
    ]);

    DB::beginTransaction();

    try {
        $companyId = $request->company_id;

        $officeshift = OfficeShift::select('id', 'name')
            ->where('company_id', $companyId)
            ->get();

        DB::commit();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Office Shift fetch successfully.',
            'data' => $officeshift
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}

    // ===============================Employee CSV Upload====================
public function uploadEmployeeCSV(Request $request)
{

     
    // Validate the file and external fields
    $validator = Validator::make($request->all(), [
        'file' => 'required|mimes:csv,txt,xlsx,xls|max:2048',
        'company_id' => 'required|integer|exists:companies,id',
        'department_id' => 'required|integer|exists:departments,id',
        'designation_id' => 'required|integer|exists:designations,id',
        'office_shift_id' => 'required|integer|exists:office_shifts,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid input',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $fileData = Excel::toArray([], $request->file('file'));

        if (empty($fileData) || empty($fileData[0])) {
            return response()->json([
                'success' => false,
                'message' => 'The uploaded file is empty'
            ], 422);
        }

        $rows = $fileData[0];
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        array_shift($rows); // Remove header row

        // Only required from CSV
        $requiredFields = ['firstname', 'lastname', 'gender', 'email', 'phone'];

        $processed = 0;
        $errors = [];

        DB::beginTransaction();

        foreach ($rows as $index => $row) {
            $row = array_map('trim', $row);

            if (empty(array_filter($row))) {
                continue;
            }

            $data = array_combine($header, $row);

            // Validate presence of required fields
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $data)) {
                    $errors[] = "Row " . ($index + 2) . ": Missing required field - " . $field;
                    continue 2;
                }
            }

            $validator = Validator::make($data, [
                'email' => 'required|email|unique:employees,email',
                'phone' => 'required|unique:employees,phone',
            ]);

            if ($validator->fails()) {
                $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            // Create employee
            Employee::create([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'gender' => $data['gender'],
                'company_id' => $request->company_id,
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'office_shift_id' => $request->office_shift_id,
                'password' => Hash::make('khannasales'),
                'birth_date' => $data['birth_date'] ?? null,
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'district' => $data['district'] ?? null,
                'state' => $data['state'] ?? null,
                'joining_date' => $data['joining_date'] ?? null,
                'basic_salary' => $data['basic_salary'] ?? 0
            ]);

            $processed++;
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Employees added successfully',
            'processed' => $processed,
            'errors' => $errors
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error processing file',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
