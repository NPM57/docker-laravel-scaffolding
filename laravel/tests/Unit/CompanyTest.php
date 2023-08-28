<?php

namespace Tests\Unit;

use App\Mail\NewCompanyNotification;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use DatabaseTransactions;

    private function get_expected_response_get_company_list($company1, $company2, $company3, $company4, $company5): array
    {
        return [
            "current_page" => 1,
            "data" => [
                [
                    "id" => $company5->id,
                    "email" => $company5->email,
                    "logo" => $company5->logo,
                    "name" => $company5->name,
                    "website" => $company5->website,
                    "logo_url" => $company5->logo_url
                ],
                [
                    "id" => $company4->id,
                    "email" => $company4->email,
                    "logo" => $company4->logo,
                    "name" => $company4->name,
                    "website" => $company4->website,
                    "logo_url" => $company4->logo_url
                ],
                [
                    "id" => $company3->id,
                    "email" => $company3->email,
                    "logo" => $company3->logo,
                    "name" => $company3->name,
                    "website" => $company3->website,
                    "logo_url" => $company3->logo_url
                ],
                [
                    "id" => $company2->id,
                    "email" => $company2->email,
                    "logo" => $company2->logo,
                    "name" => $company2->name,
                    "website" => $company2->website,
                    "logo_url" => $company2->logo_url
                ],
                [
                    "id" => $company1->id,
                    "email" => $company1->email,
                    "logo" => $company1->logo,
                    "name" => $company1->name,
                    "website" => $company1->website,
                    "logo_url" => $company1->logo_url
                ],
            ],
            "first_page_url" => env('APP_URL') . "/api/company?page=1",
            "from" => 1,
            "last_page" => 1,
            "last_page_url" => env('APP_URL') . "/api/company?page=1",
            "links" => [
                [
                    "url" => null,
                    "label" => "&laquo; Previous",
                    "active" => false
                ],
                [
                    "url" => env('APP_URL') . "/api/company?page=1",
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
            "path" => env('APP_URL') . "/api/company",
            "per_page" => 5,
            "prev_page_url" => null,
            "to" => 5,
            "total" => 5,
        ];
    }

    public function test_get_company_list()
    {
        // Mock data
        $user = User::factory()->create();

        $company1 = Company::factory()->create(['logo' => 'company_logo_1.png']);
        $company2 = Company::factory()->create(['logo' => 'company_logo_2.png']);
        $company3 = Company::factory()->create(['logo' => 'company_logo_3.png']);
        $company4 = Company::factory()->create(['logo' => 'company_logo_4.png']);
        $company5 = Company::factory()->create(['logo' => 'company_logo_5.png']);

        // Simulate Authentication
        Sanctum::actingAs($user);

        $response = $this->getJson('api/company?limit=5&page=1');
        $response->assertStatus(Response::HTTP_OK);

        $data = $response->json();
        $expectedResponse = $this->get_expected_response_get_company_list($company1, $company2, $company3, $company4, $company5);
        $this->assertEquals($expectedResponse, $data);
    }

    public function test_create_company()
    {
        // Fake Mail
        Mail::fake();
        // Fake Storage
        Storage::fake('public');

        // Mock data
        $user = User::factory()->create();

        // Prepare Data
        $photoName = 'testLogo.png';
        $image = UploadedFile::fake()->image($photoName);

        $hashWithoutExtension = substr($image->hashName(), 0, strpos($image->hashName(), '.'));
        $newCompanyData = [
            'name' => 'test',
            'email' => 'test@gmail.com',
            'logo' => $image,
            'website' => 'http://test.com',
        ];

        // Simulate Authentication
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/company/create', $newCompanyData);
        $response->assertStatus(Response::HTTP_CREATED);

        Mail::assertSent(NewCompanyNotification::class);

        $newCompany = Company::find($response->json()['new_company_id'])->first();
        $this->assertEquals($newCompany->name, $newCompanyData['name']);
        $this->assertEquals($newCompany->email, $newCompanyData['email']);
        $this->assertEquals($newCompany->logo, $hashWithoutExtension . '_' . $photoName);
        $this->assertEquals($newCompany->website, $newCompanyData['website']);
        $this->assertEquals($newCompany->logo_url, env('APP_URL'). '/storage/' . $hashWithoutExtension . '_' . $photoName);
        Storage::disk('public')->assertExists($hashWithoutExtension . '_' . $photoName);
    }

    public function test_update_company()
    {
        // Fake Storage
        Storage::fake('public');

        // Mock data
        $user = User::factory()->create();

        // Simulate Authentication
        Sanctum::actingAs($user);

        // Create a new company
        $newPhotoName = 'createLogo.png';
        $createImage = UploadedFile::fake()->image($newPhotoName);

        $createImageHashWithoutExtension = substr($createImage->hashName(), 0, strpos($createImage->hashName(), '.'));
        $newCompanyData = [
            'name' => 'test',
            'email' => 'test@gmail.com',
            'logo' => $createImage,
            'website' => 'http://test.com',
        ];

        $responseCreate = $this->postJson('/api/company/create', $newCompanyData);
        $responseCreate->assertStatus(Response::HTTP_CREATED);
        Storage::disk('public')->assertExists($createImageHashWithoutExtension . '_' . $newPhotoName);

        // Prepare data to update
        $photoName = 'testLogo.png';
        $image = UploadedFile::fake()->image($photoName);

        $hashWithoutExtension = substr($image->hashName(), 0, strpos($image->hashName(), '.'));
        $editCompanyData = [
            'id' => $responseCreate->json()['new_company_id'],
            'name' => 'edited',
            'email' => 'edited@gmail.com',
            'logo' => $image,
            'website' => 'http://edited.com',
        ];

        $response = $this->postJson('/api/company/edit', $editCompanyData);
        $response->assertStatus(Response::HTTP_CREATED);
        $editedCompany = Company::find($response->json()['updated_company_id'])->first();
        $this->assertEquals($editedCompany->name, $editCompanyData['name']);
        $this->assertEquals($editedCompany->email, $editCompanyData['email']);
        $this->assertEquals($editedCompany->logo, $hashWithoutExtension . '_' . $photoName);
        $this->assertEquals($editedCompany->website, $editCompanyData['website']);
        $this->assertEquals($editedCompany->logo_url, env('APP_URL'). '/storage/' . $hashWithoutExtension . '_' . $photoName);
        Storage::disk('public')->assertExists($hashWithoutExtension . '_' . $photoName);
        Storage::disk('public')->assertMissing($createImageHashWithoutExtension . '_' . $newPhotoName);
    }

    public function test_delete_company()
    {
        // Fake Storage
        Storage::fake('public');

        // Mock data
        $user = User::factory()->create();
        $company = Company::factory()->create();

        // Simulate Authentication
        Sanctum::actingAs($user);

        $deleteCompanyData = [
            'id' => $company->id,
        ];

        $this->assertEquals(Company::all()->count(), 1);

        $response = $this->deleteJson('/api/company/', $deleteCompanyData);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals(Company::all()->count(), 0);
    }
}
