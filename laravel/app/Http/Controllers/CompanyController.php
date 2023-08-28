<?php

namespace App\Http\Controllers;

use App\Mail\NewCompanyNotification;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class CompanyController extends Controller
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
        $query = Company::query();
        $search = $request->query('search');
        if ($search) {
            // simple where here or another scope, whatever you like
            $query->where('name', 'like', '%' . $search . '%');
        }
        $query->orderBy('id','desc');
        return $query->paginate($request->limit, ['id', 'email', 'logo', 'name', 'website'], 'page', $request->page);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:company,email',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:png',
        ])->validate();

        try {
            $newCompany = new Company;
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $hashWithoutExtension = substr($logo->hashName(), 0, strpos($logo->hashName(), '.'));
                $filename = $hashWithoutExtension . '_' . $logo->getClientOriginalName();
                Image::make($logo)->resize(100, 100)->save(Storage::disk('public')->path($filename));
                $newCompany->logo = $filename;
            };

            $newCompany->name = $request->name;
            $newCompany->email = $request->email;
            $newCompany->website = $request->website;
            $newCompany->save();

            Mail::to('admin@admin.com')->send(new NewCompanyNotification($newCompany));
            return response()->json([
                'message' => 'A company has been created successfully',
                'new_company_id' => $newCompany->id,
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
            'id' => 'required|int|exists:company,id',
            'name' => 'required|string',
            'email' => 'required|email|unique:company,email,' . $request->id,
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:png',
        ])->validate();

        try {
            $editCompany = Company::find($request->id);
            $oldLogoPath = $editCompany->logo;
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $hashWithoutExtension = substr($logo->hashName(), 0, strpos($logo->hashName(), '.'));
                $filename = $hashWithoutExtension . '_' . $logo->getClientOriginalName();
                Image::make($logo)->resize(100, 100)->save(Storage::disk('public')->path($filename));
                $editCompany->logo = $filename;
            };

            $editCompany->name = $request->name;
            $editCompany->email = $request->email;
            $editCompany->website = $request->website;
            $editCompany->save();

            // Remove old logo if new logo has been saved
            if ($request->hasFile('logo')) {
                if (Storage::disk('public')->exists($oldLogoPath)) {
                    Storage::disk('public')->delete($oldLogoPath);
                }
            }

            return response()->json([
                'message' => 'The selected company has been updated successfully',
                'updated_company_id' => $editCompany->id,
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
            'id' => 'required|int|exists:company,id',
        ])->validate();

        try {
            $companyId = $request->get('id');
            Employee::where('company_id', '=', $companyId)->delete();
            Company::findOrFail($companyId)->delete();
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
