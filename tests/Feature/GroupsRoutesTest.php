<?php

namespace Tests\Feature;

use App\Enums\Course;
use App\Enums\EducationForm;
use App\Models\Institute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupsRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_groups_search_validation_errors_when_missing_required_fields(): void
    {
        $response = $this->getJson('/groups/search');
        $response->assertStatus(422);
    }

    public function test_groups_search_returns_404_when_no_results(): void
    {
        $institute = Institute::create(['name' => 'Tech']);

        $query = http_build_query([
            'name' => null,
            'course' => Course::First->value,
            'education_form' => EducationForm::Intramural->value,
            'institute_id' => $institute->id,
            'discipline_id' => 1,
        ]);

        $response = $this->getJson('/groups/search?' . $query);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Группы не найдены',
                'status' => 404,
            ]);
    }
}


