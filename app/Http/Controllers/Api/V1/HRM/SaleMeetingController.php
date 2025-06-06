<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Employee;
use DB;




class SaleMeetingController extends Controller
{
    public function meeting()
    {
        try {
            // Fetch all meetings with employee and related details
            $records = DB::table('meetings')
                ->join('employees', 'meetings.emp_id', '=', 'employees.id')
                ->join('companies', 'employees.company_id', '=', 'companies.id')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->join('designations', 'employees.designation_id', '=', 'designations.id')
                ->select(
                    'employees.id as emp_id',
                    'employees.firstname',
                    'employees.lastname',
                    'employees.email',
                    'employees.phone',
                    'companies.name as company_name',
                    'departments.department as department_name',
                    'designations.designation',
                    'employees.state',
    
                    // Meeting-specific fields
                    'meetings.id as meeting_id',
                    'meetings.role',
                    'meetings.dealer',
                    'meetings.data',
                    'meetings.image',
                    'meetings.time',
                    'meetings.latitude',
                    'meetings.longitude',
                    'meetings.date',
                    'meetings.agent_type',
    
                    // Optional extended meeting fields (e.g., for farmers/distributors)
                    'meetings.farmer_name',
                    'meetings.dob',
                    'meetings.anniversay_date',
                    'meetings.address',
                    'meetings.cattle_buff',
                    'meetings.cattle_cow',
                    'meetings.cattle_calf',
                    'meetings.trail_bag',
                    'meetings.schedule',
                    'meetings.meeting_note',
    
                    'meetings.dealer_name',
                    'meetings.firm_name',
                    'meetings.brand',
                    'meetings.sale'
                )
                ->orderBy('meetings.id', 'desc')
                ->get();
    
            // Group by employee
            $grouped = $records->groupBy('emp_id');
    
            $result = [];
    
            foreach ($grouped as $empId => $meetings) {
                $first = $meetings->first();
    
                $userMeetings = $meetings->map(function ($meeting) {
                    $base = [
                        'meeting_id' => $meeting->meeting_id,
                        'date' => $meeting->date,
                        'time' => $meeting->time,
                        'agent_type' => $meeting->agent_type,
                        'role' => $meeting->role,
                    ];
    
                    if ($meeting->role === 'Farmer') {
                        return array_merge($base, [
                            'dealer' => $meeting->dealer,
                            'farmer_name' => $meeting->farmer_name,
                            'latitude' => $meeting->latitude,
                            'longitude' => $meeting->longitude,
                            'image' => $meeting->image ? asset('storage/' . $meeting->image) : null,
                            'dob' => $meeting->dob,
                            'anniversay_date' => $meeting->anniversay_date,
                            'phone' => $meeting->phone,
                            'address' => $meeting->address,
                            'cattle_buff' => $meeting->cattle_buff,
                            'cattle_cow' => $meeting->cattle_cow,
                            'cattle_calf' => $meeting->cattle_calf,
                            'trail_bag' => $meeting->trail_bag,
                            'schedule' => $meeting->schedule,
                            'meeting_note' => $meeting->meeting_note,
                            'state' => $meeting->state,
                        ]);
                    } elseif ($meeting->role === 'Dealer') {
                        return array_merge($base, [
                            'data' => $meeting->data,
                            'dealer' => $meeting->dealer,
                            'image' => $meeting->image ? asset('storage/' . $meeting->image) : null,
                            'state' => $meeting->state,
                            'dealer_name' => $meeting->dealer_name,
                            'firm_name' => $meeting->firm_name,
                            'brand' => $meeting->brand,
                            'sale' => $meeting->sale,
                            'meeting_note' => $meeting->meeting_note,
                        ]);
                    } else {
                        return array_merge($base, [
                            'dealer' => $meeting->dealer,
                            'latitude' => $meeting->latitude,
                            'longitude' => $meeting->longitude,
                            'image' => $meeting->image ? asset('storage/' . $meeting->image) : null,
                            'meeting_note' => $meeting->meeting_note,
                        ]);
                    }
                });
    
                // Count per date
                $dailyCounts = $meetings->groupBy('date')->map(function ($items, $date) {
                    return [
                        'date' => $date,
                        'meeting_count' => $items->count()
                    ];
                })->values();
    
                $result[] = [
                    'employee_id' => $empId,
                    'name' => $first->firstname . ' ' . $first->lastname,
                    'email' => $first->email,
                    'phone' => $first->phone,
                    'company' => $first->company_name,
                    'department' => $first->department_name,
                    'designation' => $first->designation,
                    'state' => $first->state,
                    'meetings' => $userMeetings,
                    'daily_meeting_summary' => $dailyCounts
                ];
            }
    
            return response()->json([
                'success' => true,
                'status_code' => 200, 
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ]);
        }
    }
    
    

}
