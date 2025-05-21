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
        

        try{
            
      $this->validate($request, [
        'firstname'      => 'required|string',
        'lastname'       => 'required|string',
        'gender'         => 'required',
        'company_id'     => 'required',
        'department_id'  => 'required',
        'designation_id' => 'required',
        'office_shift_id'=> 'required',
        'email'          => 'required|email|unique:employees,email',
    ]);

        $employe                    =       new Employee();
        $employe->firstname         =       $request->input('firstname');
        $employe->lastname          =       $request->input('lastname');
        $employe->username          =       $request->input('firstname') . " " .$request->input('lastname');
        $employe->phone             =       $request->input('phone');
        $employe->gender            =       $request->input('gender');
        $employe->birth_date        =       $request->input('birth_date');
        $employe->email             =       $request->input('email');
        $employe->country           =       $request->input('country');
        $employe->city              =       $request->input('city');
        $employe->district          =       $request->input('district');
        $employe->village           =       $request->input('village');
        $employe->state             =       $request->input('state');
        $employe->joining_date      =       $request->input('joining_date');
        $employe->company_id        =       $request->input('company_id');
        $employe->department_id     =       $request->input('department_id');
        $employe->designation_id    =       $request->input('designation_id');
        $employe->office_shift_id   =       $request->input('office_shift_id');
        $employe->basic_salary     =       $request->input('basic_salary');
        $employe->password         =       Hash::make('12345678');
        $employe->save();
        
        if($employe->designation_id == 1){
            $gm                 =       new Agmdetail();
            $gm->name           =       $employe->firstname . $employe->lastname;
            $gm->phone          =       $employe->phone;
            $gm->gender         =       $employe->gender;
            $gm->email          =       $employe->email;
            $gm->state          =       $employe->state;
            $gm->district       =       $employe->district;
            $gm->company_id     =       $employe->company_id;
            $gm->department_id  =       $employe->department_id;
            $gm->save();
        }
        else if($employe->designation_id == 2)
        {
            $agm                 =       new GmDetail();
            $agm->name           =       $employe->firstname . $employe->lastname;
            $agm->phone          =       $employe->phone;
            $agm->gender         =       $employe->gender;
            $agm->email          =       $employe->email;
            $agm->state          =       $employe->state;
            $agm->district       =       $employe->district;
            $agm->company_id     =       $employe->company_id;
            $agm->department_id  =       $employe->department_id;
            $agm->save();
        }

        DB::commit();
         return response()->json([
            'success'       =>   true,
            'status_code'   =>   200,
            'message'       =>   'Employee added successfully',
            'data'          =>    $employe 
         ]);

        }

        catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'success'     =>   false,
                'status_code' =>    500,
                'message'     =>   'Something Went Wrong' .$e->getMessage(), 

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
    print_r($_REQUEST);
    die;
    // Validate the file upload and common fields
    $validator = Validator::make($request->all(), [
        'file' => 'required|mimes:csv,txt,xlsx,xls|max:2048',
        'company_id' => 'required|integer|exists:companies,id',
        'department_id' => 'required|integer|exists:departments,id',
        'designation_id' => 'required|integer|exists:designations,id',
        'office_shift_id' => 'required|integer|exists:office_shifts,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid input',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Parse uploaded file
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

        $requiredFields = ['firstname', 'lastname', 'gender', 'email', 'phone'];

        $processed = 0;
        $errors = [];

        // Extract common data
        $commonData = [
            'company_id' => $request->input('company_id'),
            'department_id' => $request->input('department_id'),
            'designation_id' => $request->input('designation_id'),
            'office_shift_id' => $request->input('office_shift_id')
        ];

        DB::beginTransaction();

        foreach ($rows as $index => $row) {
            $row = array_map('trim', $row);

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $data = array_combine($header, $row);

            // Check required fields in row
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $data) || empty($data[$field])) {
                    $errors[] = "Row " . ($index + 2) . ": Missing required field - " . $field;
                    continue 2;
                }
            }

            // Validate unique email and phone
            $rowValidator = Validator::make($data, [
                'email' => 'required|email|unique:employees,email',
                'phone' => 'required|unique:employees,phone',
            ]);

            if ($rowValidator->fails()) {
                $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $rowValidator->errors()->all());
                continue;
            }

            // Create employee with merged data
            Employee::create(array_merge($commonData, [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'gender' => $data['gender'],
                'password' => Hash::make('12345678'),
                // Optional fields
                'birth_date' => $data['birth_date'] ?? null,
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'district' => $data['district'] ?? null,
                'state' => $data['state'] ?? null,
                'joining_date' => $data['joining_date'] ?? null,
                'basic_salary' => $data['basic_salary'] ?? 0,
            ]));

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
