<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminDownloadsTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.downloads
     * 
     */
    public function can_open_downloads_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.downloads.index'));   
        $response->assertOk();
    }
}
