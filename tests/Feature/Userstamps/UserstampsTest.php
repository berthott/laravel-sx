<?php

namespace berthott\SX\Tests\Feature\Userstamps;

class UserstampsTest extends UserstampsTestCase
{
    public function test_userstamps(): void
    {
        $user1 = User::create();
        $user2 = User::create();
        $this->actingAs($user1);

        // create
        $id = $this->post(route('entities.store'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    's_2' => 3333, // double
                ]
            ])
            ->assertStatus(200)['id'];
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
        ]);
        
        // import
        $this->post(route('entities.import'));
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
            'created_by' => $user1->id,
            'updated_by' => $user1->id,
        ]);

        $this->actingAs($user2);
        // update
        $this->put(route('entities.update', [
                'entity' => $id,
                'form_params' => [
                    's_2' => 4444,
                ],
            ]))
            ->assertStatus(200);
        
        // import
        $this->post(route('entities.import'));
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
            'created_by' => $user1->id,
            'updated_by' => $user2->id,
        ]);

        // delete
        $this->delete(route('entities.destroy', ['entity' => $id]))
            ->assertStatus(200)
            ->assertSeeText('Success');
        $this->assertDatabaseMissing('entities', ['respondentid' => $id]);
    }
}
