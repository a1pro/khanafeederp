<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // ✅ Required for authentication
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable // ✅ This line is key
{
    use HasApiTokens,HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id','firstname','lastname','username','email','password','gender','phone','remaining_leave','total_leave',
        'birth_date','department_id','designation_id','office_shift_id','joining_date',
        'leaving_date','marital_status','employment_type','city','province','zipcode','address','resume','avatar','document','basic_salary',
        'country','company_id','facebook','skype','whatsapp','twitter','linkedin','hourly_rate',
    ];

    protected $casts = [
        'id'     => 'integer',
        'company_id'     => 'integer',
        'department_id'  => 'integer',
        'designation_id' => 'integer',
        'office_shift_id' => 'integer',
        'hourly_rate' => 'double',
        'basic_salary' => 'integer',
        'remaining_leave' => 'integer',
        'total_leave' => 'integer',
    ];


    public function company()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_id');
    }

    public function department()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }

    public function designation()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation_id');
    }

    public function office_shift()
    {
        return $this->hasOne('App\Models\OfficeShift', 'id', 'office_shift_id');
    }

    
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leave()
    {
        return $this->hasMany(Leave::class)
        ->select('id','employee_id','start_date','end_date','status')
        ->where('status' , 'approved');
    }

}
