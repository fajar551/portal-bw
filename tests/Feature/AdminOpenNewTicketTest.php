<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminOpenNewTicketTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     * @group admin
     * @group admin.opennewtickets
     * 
     */
    public function can_open_new_ticket_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.support.opennewtickets.index'));   
        $response->assertOk();
    }

    /**
     * @testX
     * @group admin
     * @group admin.opennewtickets
     * 
     */
    public function can_open_new_ticket()
    {
        $response = $this->actingAs($this->admin, 'admin')
        // ->followingRedirects()
        ->post(route("admin.pages.support.opennewtickets.store"), [
            'clientid' => $this->client->id,
            'deptid' => 1,
            'subject' => $this->faker->text(50),
            'message' => $this->faker->paragraph(),
        ]);

        $response->assertStatus(302);
    }

    /**
     * @test
     * @group admin
     * @group admin.opennewtickets
     * 
     */
    public function can_open_new_ticket_attachment()
    {
        Storage::fake('attachments');

        // $filename = Str::random(6)."_avatar.jpg";
        // $file = UploadedFile::fake()->image($filename);
        $filename = 'placeholder.png';
        $filepath = public_path($filename);

        $response = $this->actingAs($this->admin, 'admin')
        // ->followingRedirects()
        ->post(route("admin.pages.support.opennewtickets.store"), [
            'clientid' => $this->client->id,
            'deptid' => 1,
            'subject' => $this->faker->text(50),
            'message' => $this->faker->paragraph(),
            'attachments' => $filename,
            // 'attachments' => new \Illuminate\Http\UploadedFile($filepath, $filename, null, null, true),
        ]);

        // Assert the file was stored...
        // Storage::disk('attachments')->assertExists($filename);

        $response->assertStatus(302);
    }
}
