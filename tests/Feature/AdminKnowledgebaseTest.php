<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminKnowledgebaseTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     * @group admin
     * @group admin.knowledgebase
     * 
     */
    public function can_open_knowledgebase_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.support.knowledgebase.index', ['id' => 0]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.knowledgebase
     * 
     */
    public function can_add_new_category()
    {
        $response = $this->actingAs($this->admin, 'admin')
        // ->followingRedirects()
        ->post(route("admin.pages.support.knowledgebase.categoryStore"), [
            'name' => $this->faker->name(),
            'description' => $this->faker->paragraph(),
        ]);

        $response->assertStatus(302);
    }

    /**
     * @test
     * @group admin
     * @group admin.knowledgebase
     * 
     */
    public function can_add_new_article()
    {
        $response = $this->actingAs($this->admin, 'admin')
        // ->followingRedirects()
        ->post(route("admin.knowledgebasearticleStore"), [
            'articlename' => $this->faker->name(),
        ]);

        $response->assertStatus(302);
    }
}
