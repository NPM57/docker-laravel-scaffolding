<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use DatabaseTransactions;

    private function get_expected_response_get_employee_list($employee1, $employee2, $employee3, $employee4, $employee5, $company1, $company2): array
    {
        return [
            "current_page" => 1,
            "data" => [
                [
                    "id" => $employee5->id,
                    "first_name" => $employee5->first_name,
                    "last_name" => $employee5->last_name,
                    "email" => $employee5->email,
                    "phone" => $employee5->phone,
                    "company_id" => $company2->id,
                    "company" => [
                        "id" => $company2->id,
                        "logo" => $company2->logo,
                        "name" => $company2->name,
                        "website" => $company2->website,
                        "email" => $company2->email,
                        "logo_url" => $company2->logo_url
                    ]
                ],
                [
                    "id" => $employee4->id,
                    "first_name" => $employee4->first_name,
                    "last_name" => $employee4->last_name,
                    "email" => $employee4->email,
                    "phone" => $employee4->phone,
                    "company_id" => $company2->id,
                    "company" => [
                        "id" => $company2->id,
                        "logo" => $company2->logo,
                        "name" => $company2->name,
                        "website" => $company2->website,
                        "email" => $company2->email,
                        "logo_url" => $company2->logo_url
                    ]
                ],
                [
                    "id" => $employee3->id,
                    "first_name" => $employee3->first_name,
                    "last_name" => $employee3->last_name,
                    "email" => $employee3->email,
                    "phone" => $employee3->phone,
                    "company_id" => $company1->id,
                    "company" => [
                        "id" => $company1->id,
                        "logo" => $company1->logo,
                        "name" => $company1->name,
                        "website" => $company1->website,
                        "email" => $company1->email,
                        "logo_url" => $company1->logo_url
                    ]
                ],
                [
                    "id" => $employee2->id,
                    "first_name" => $employee2->first_name,
                    "last_name" => $employee2->last_name,
                    "email" => $employee2->email,
                    "phone" => $employee2->phone,
                    "company_id" => $company1->id,
                    "company" => [
                        "id" => $company1->id,
                        "logo" => $company1->logo,
                        "name" => $company1->name,
                        "website" => $company1->website,
                        "email" => $company1->email,
                        "logo_url" => $company1->logo_url
                    ]
                ],
                [
                    "id" => $employee1->id,
                    "first_name" => $employee1->first_name,
                    "last_name" => $employee1->last_name,
                    "email" => $employee1->email,
                    "phone" => $employee1->phone,
                    "company_id" => $company1->id,
                    "company" => [
                        "id" => $company1->id,
                        "logo" => $company1->logo,
                        "name" => $company1->name,
                        "website" => $company1->website,
                        "email" => $company1->email,
                        "logo_url" => $company1->logo_url
                    ]
                ],
            ],
            "first_page_url" => env('APP_URL') . "/api/employee?page=1",
            "from" => 1,
            "last_page" => 1,
            "last_page_url" => env('APP_URL') . "/api/employee?page=1",
            "links" => [
                [
                    "url" => null,
                    "label" => "&laquo; Previous",
                    "active" => false
                ],
                [
                    "url" => env('APP_URL') . "/api/employee?page=1",
                    "label" => "1",
                    "active" => true
                ],
                [
                    "url" => null,
                    "label" => "Next &raquo;",
                    "active" => false
                ]
            ],
            "next_page_url" => null,
            "path" => env('APP_URL') . "/api/employee",
            "per_page" => 5,
            "prev_page_url" => null,
            "to" => 5,
            "total" => 5,
        ];
    }

    public function test_get_employee_list()
    {
        // Mock data
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $employee1 = Employee::factory()->create(['company_id' => $company1->id]);
        $employee2 = Employee::factory()->create(['company_id' => $company1->id]);
        $employee3 = Employee::factory()->create(['company_id' => $company1->id]);
        $employee4 = Employee::factory()->create(['company_id' => $company2->id]);
        $employee5 = Employee::factory()->create(['company_id' => $company2->id]);

        // Simulate Authentication
        Sanctum::actingAs($user);

        $response = $this->getJson('api/employee?limit=5&page=1');
        $response->assertStatus(Response::HTTP_OK);

        $data = $response->json();
        $expectedResponse = $this->get_expected_response_get_employee_list($employee1, $employee2, $employee3, $employee4, $employee5, $company1, $company2);
        $this->assertEquals($expectedResponse, $data);
    }

    public function test_create_employee()
    {
        // Mock data
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $newEmployeeData = [
            'first_name' => 'test employee first name',
            'last_name' => 'test employee last name',
            'email' => 'employee@email.com',
            'phone' => '0410000000',
            'company_id' => $company->id,
        ];

        // Simulate Authentication
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/employee/create', $newEmployeeData);
        $response->assertStatus(Response::HTTP_CREATED);

        $newEmployee = Employee::find($response->json()['new_employee_id'])->first();
        $this->assertEquals($newEmployee->first_name, $newEmployeeData['first_name']);
        $this->assertEquals($newEmployee->last_name, $newEmployeeData['last_name']);
        $this->assertEquals($newEmployee->email, $newEmployeeData['email']);
        $this->assertEquals($newEmployee->phone, $newEmployeeData['phone']);
        $this->assertEquals($newEmployee->company_id, $newEmployeeData['company_id']);
    }

    public function test_update_employee()
    {
        // Mock data
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $newEmployeeData = [
            'first_name' => 'test employee first name',
            'last_name' => 'test employee last name',
            'email' => 'employee@email.com',
            'phone' => '0410000000',
            'company_id' => $company->id,
        ];

        // Simulate Authentication
        Sanctum::actingAs($user);

        $responseCreate = $this->postJson('/api/employee/create', $newEmployeeData);
        $responseCreate->assertStatus(Response::HTTP_CREATED);

        $editEmployeeData = [
            'id' => $responseCreate->json()['new_employee_id'],
            'first_name' => 'test update employee first name',
            'last_name' => 'test update employee last name',
            'email' => 'updateEmployee@email.com',
            'phone' => '0410000000',
            'company_id' => $company2->id,
        ];

        $response = $this->postJson('/api/employee/edit', $editEmployeeData);
        $response->assertStatus(Response::HTTP_CREATED);

        $editedEmployee = Employee::find($response->json()['updated_employee_id'])->first();
        $this->assertEquals($editedEmployee->first_name, $editEmployeeData['first_name']);
        $this->assertEquals($editedEmployee->last_name, $editEmployeeData['last_name']);
        $this->assertEquals($editedEmployee->email, $editEmployeeData['email']);
        $this->assertEquals($editedEmployee->phone, $editEmployeeData['phone']);
        $this->assertEquals($editedEmployee->company_id, $editEmployeeData['company_id']);
        $this->assertEquals($editedEmployee->company->id, $company2->id);
        $this->assertEquals($editedEmployee->company->name, $company2->name);
        $this->assertEquals($editedEmployee->company->logo, $company2->logo);
        $this->assertEquals($editedEmployee->company->email, $company2->email);
        $this->assertEquals($editedEmployee->company->website, $company2->website);
        $this->assertEquals($editedEmployee->company->logo_url, $company2->logo_url);
    }

    public function test_delete_employee()
    {
        // Mock data
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Simulate Authentication
        Sanctum::actingAs($user);

        $deleteEmployeeData = [
            'id' => $employee->id,
        ];

        $this->assertEquals(Employee::all()->count(), 1);

        $response = $this->deleteJson('/api/employee/', $deleteEmployeeData);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals(Employee::all()->count(), 0);
    }
}
