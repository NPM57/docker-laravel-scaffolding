<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        return Employee::with(['company' => function ($q) {
            $q->select(
                'id', 'logo', 'name', 'website', 'email'
            );
        }])
            ->orderBy('id','desc')
            ->paginate(
                $request->limit,
                ['id', 'first_name', 'last_name', 'email', 'phone', 'company_id'],
                'page',
                $request->page
            );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'nullable|email|unique:employee,email',
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'company_id' => 'required|numeric|exists:company,id',
        ])->validate();

        try {
            $newEmployee = new Employee;

            $newEmployee->first_name = $request->first_name;
            $newEmployee->last_name = $request->last_name;
            $newEmployee->email = $request->email;
            $newEmployee->phone = $request->phone;
            $newEmployee->company_id = $request->company_id;
            $newEmployee->save();

            return response()->json([
                'message' => 'An employee has been created successfully',
                'new_employee_id' => $newEmployee->id,
            ], 201);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], $exception->getStatusCode());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Validator::make($request->all(), [
            'id' => 'required|int|exists:employee,id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'nullable|email|unique:employee,email,' . $request->id,
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'company_id' => 'nullable|numeric|exists:company,id',
        ])->validate();

        try {
            $editEmployee = Employee::find($request->id);
            $editEmployee->first_name = $request->first_name;
            $editEmployee->last_name = $request->last_name;
            $editEmployee->email = $request->email;
            $editEmployee->phone = $request->phone;
            $editEmployee->company_id = $request->company_id;
            $editEmployee->save();

            return response()->json([
                'message' => 'The selected employee has been updated successfully',
                'updated_employee_id' => $editEmployee->id,
            ], 201);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], $exception->getStatusCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Validator::make($request->all(), [
            'id' => 'required|int|exists:employee,id',
        ])->validate();

        try {
            Employee::whereId($request->get('id'))->delete();
            return response()->json([
                'message' => 'The selected company has been deleted'
            ], 200);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], $exception->getStatusCode());
        }
    }
}
