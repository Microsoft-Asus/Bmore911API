<?php

namespace Tests\Feature;

use Carbon\Carbon;
use App\Models\Call;
use App\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EndpointTest extends TestCase
{

    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        factory(Call::class)->states('today', 'district-SE', 'low-priority')->create();
        factory(Call::class)->states('this-week', 'district-NW', 'high-priority')->create();
        factory(Call::class)->states('this-month', 'high-priority')->create();

    }

    public function testRecordCountToday(){
        $response = $this->get($this->baseUrl . '/records/count/today');
        $response
            ->assertSuccessful()
            ->assertJsonStructure(['status', 'data'])
            ->assertExactJson(['status' => 'success', 'data' => 1]);
    }

    public function testRecordCountWeek(){
        $response = $this->get($this->baseUrl . '/records/count/week');
        $response
            ->assertSuccessful()
            ->assertJsonStructure(['status', 'data'])
            ->assertExactJson(['status' => 'success', 'data' => 3]);
    }

    public function testRecordCountMonth(){
        $response = $this->get($this->baseUrl . '/records/count/month');
        $response
            ->assertSuccessful()
            ->assertJsonStructure(['status', 'data'])
            ->assertExactJson(['status' => 'success', 'data' => 3]);
    }

    public function testRecordCountYear(){
        $response = $this->get($this->baseUrl . '/records/count/year');
        $response
            ->assertSuccessful()
            ->assertJsonStructure(['status', 'data'])
            ->assertExactJson(['status' => 'success', 'data' => 3]);
    }

    public function testRecordCountAll(){
        $response = $this->get($this->baseUrl . '/records/count/all');
        $response
            ->assertSuccessful()
            ->assertJsonStructure(['status', 'data' => ['today', 'week', 'month', 'year']])
            ->assertExactJson(['status' => 'success', 'data' => ['today' => 1, 'week' => 3, 'month' => 3, 'year' => 3]]);
    }

    public function testRecordSearchNoJson(){
        
        $jsonData = [

        ];

        $response = $this->json('POST', $this->baseUrl . '/records/search', $jsonData);
        $response
            ->assertStatus(400)
            ->assertJsonStructure(['status', 'data'])
            ->assertJson(['status' => 'failed']);
    }

    public function testAvailabilityOfFactoryModelsInDB(){
        $this->assertDatabaseHas('calls', ['district' => 'NE']);
        $this->assertDatabaseHas('calls', ['district' => 'SE']);
        $this->assertDatabaseHas('calls', ['district' => 'NW']);
        $this->assertDatabaseHas('calls', ['priority' => 3]);
        $this->assertDatabaseHas('calls', ['priority' => 1]);
    }

    public function testRecordSearchEmptyDates(){
        
        $jsonData = [
            'start_date' => "",
            'end_date' => ""
        ];

        $response = $this->json('POST', $this->baseUrl . '/records/search', $jsonData);
        $response
            ->assertStatus(400)
            ->assertJsonStructure(['status', 'data'])
            ->assertJson(['status' => 'failed']);
    }

    // public function testRecordSearchOnlyStartDate(){
    //     $start_date = Carbon::today()->toDateTimeString();
    //     var_dump($start_date);
    //     $jsonData = [
    //         'start_date' => Carbon::today()->toDateTimeString(),
    //         'end_date' => ""
    //     ];

    //     $response = $this->json('POST', $this->baseUrl . '/records/search', $jsonData);
    //     $response
    //         ->assertSuccessful()
    //         ->assertJsonStructure(['status', 'data'])
    //         ->assertJson(['district' => 'SE', 'priority' => 1]);
    // }
}
