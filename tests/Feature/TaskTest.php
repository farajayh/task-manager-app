<?php

use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Task;
use App\Models\User;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected $task;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $users = User::factory(10)->create();
        $this->user = $users->first();

        $tasks = Task::factory(20)->create();
        $this->task = $tasks->first();
    }

    /**
     * Test retrieval of paginated lists of tasks
     */
    public function test_can_retrieve_paginated_tasks(): void
    {
        $response = $this->get('/api/tasks');

        // Assert that the status code is 200
        $response->assertStatus(200);

        // Assert that the returned response has a success messsage
        $response->assertJson([
            'status' => true,
            'message' => 'Success',
        ]);

        // Assert that the returned response structure matches the expected structure
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'current_page',
                'data',
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
            ],
        ]);

    }

     /**
     * Test retrieval of tasks with page query
     */
    public function test_can_retrieve_tasks_with_page_query(): void
    {
        $response = $this->getJson('/api/tasks?page=1&per_page=10');

        // Assert that the status code is 200
        $response->assertStatus(200);

        // Assert that the returned response has a success messsage
        $response->assertJson([
           'status' => true,
           'message' => 'Success',
        ]);

        // Assert that the returned response structure matches the expected structure
        $response->assertJsonStructure([
           'status',
           'message',
            'data' => [
                'current_page',
                'data',
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
            ],
        ]);
    }

    /**
     * Test retrieval of single task
     */
    public function test_can_retrieve_single_task(): void
    {
        $response = $this->getJson("/api/tasks/{$this->task->id}");

        // Assert that the status code is 200
        $response->assertStatus(200);

        // Assert that the returned response has a success messsage
        $response->assertJson([
            'status' => true,
            'message' => 'Success',
        ]);

        // Assert that the returned response structure matches the expected structure
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);     
    }

    /**
     * Authenticated user can create a new task
     */
    public function test_authenticated_user_can_create_task(): void
    {
        $this->actingAs($this->user, 'api');

        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/tasks', [
                             'title' => 'New Task',
                             'description' => 'Task Description',
                             'due_date' => '2022-07-24'
                         ]);

        // Assert that the response status code is 201                 
        $response->assertStatus(201);
        
        // Assert that the returned response has a success messsage
        $response->assertJson([
            'status' => true,
            'message' => 'New task created successfully',
        ]);
        
        // Assert that the returned response structure matches the expected structure
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }    

    /**
     * Authenticated user cannot create a new task
     */
    public function test_unauthenticated_user_cannot_create_task(): void
    {
        $response = $this->postJson('/api/tasks', [
                             'title' => 'New Task',
                             'description' => 'Task Description',
                             'due_date' => '2022-07-24'
                         ]);

        // Assert that the response status code is 401                 
        $response->assertStatus(401);
        
        // Assert that the returned response has an error messsage
        $response->assertJson([
           'status' => false,
           'message' => 'Unauthenticated.'
        ]);
    }    

    /**
     * Test that task cannot be created without title
     */
    public function test_cannot_create_task_without_title(): void
    {
        $this->actingAs($this->user, 'api');

        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/tasks', [
                             'description' => 'Task Description',
                             'due_date' => '2022-07-24'
                         ]);

        // Assert that the response status code is 422                 
        $response->assertStatus(422);
        
        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'title' => ['The title field is required.'],
            ],
        ]);
    }

    /**
     * Test that task cannot be created without description
     */
    public function test_cannot_create_task_without_description(): void
    {
        $this->actingAs($this->user, 'api');

        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/tasks', [
                             'title' => 'New Task',
                             'due_date' => '2022-07-24'
                         ]);

        // Assert that the response status code is 422                 
        $response->assertStatus(422);
        
        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'description' => ['The description field is required.'],
            ],
        ]);
    }

    /**
     * Test that task cannot be created without due date
     */
    public function test_cannot_create_task_without_duedate(): void
    {
        $this->actingAs($this->user, 'api');

        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/tasks', [
                             'title' => 'New Task',
                             'description' => 'Task Description'
                         ]);

        // Assert that the response status code is 422                 
        $response->assertStatus(422);
        
        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'due_date' => ['The due date field is required.'],
            ],
        ]);
    }

    /**
     * Authenticated user can update a task
     */
    public function test_authenticated_user_can_update_task(): void
    {
        $this->actingAs($this->user, 'api');
        
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/tasks', [
                             'title' => 'New Task',
                             'description' => 'Task Description',
                             'due_date' => '2022-07-24'
                         ]);

        
        $response_data = $response->json();
                    
        $task_id = $response_data['data']['id'];                

        $response = $this->withHeader('Authorization', 'Bearer '. $token)
                         ->putJson("/api/tasks/$task_id", [
                             'title' => 'Updated Task',
                             'description' => 'Updated Task Description',
                             'due_date' => '2022-07-25'
                         ]);

        // Assert that the response status code is 200                 
        $response->assertStatus(200);
        
        // Assert that the returned response has a success messsage
        $response->assertJson([
            'status' => true,
            'message' => 'Task was updated successfully',
            'data' => [
                'id' => $task_id,
                'title' => 'Updated Task',
                'description' => 'Updated Task Description',
                'due_date' => '2022-07-25'
            ]
        ]);
    
    }

    /**
     * User can only update tasks they created
     */
    public function test_user_cannot_update_other_user_task(): void
    {
        $this->actingAs($this->user, 'api');

        $token = JWTAuth::fromUser($this->user);

        $task = Task::factory()->create();

        //make sure the task user id is not same as the authenticated user id
        $task->user_id = (User::factory()->create())->id;
        $task->save();
        
        $response = $this->withHeader('Authorization', 'Bearer '. $token)
                         ->putJson("/api/tasks/{$task->id}", [
                             'title' => 'Updated Task',
                             'description' => 'Updated Task Description',
                             'due_date' => '2022-07-25'
                         ]);

        // Assert that the response status code is 403                 
        $response->assertStatus(403);
        
        // Assert that the returned response has an error messsage
        $response->assertJson([
            'status' => false,
           'message' => 'Unauthorized Action'
        ]);
    }
    
    /**
     * Unauthenticated user cannot update a task
     */
    public function test_unauthenticated_user_cannot_update_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->putJson("/api/tasks/{$task->id}", [
                             'title' => 'Updated Task',
                             'description' => 'Updated Task Description',
                             'due_date' => '2022-07-25'
                         ]);

        // Assert that the response status code is 401                 
        $response->assertStatus(401);
        
        // Assert that the returned response has an error messsage
        $response->assertJson([
           'status' => false,
           'message' => 'Unauthenticated.'
        ]);
    }
    
    /**
     * Authenticated user can delete a task
     */
    public function test_authenticated_user_can_delete_task(): void
    {
        $this->actingAs($this->user, 'api');
        
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/tasks', [
                             'title' => 'New Task',
                             'description' => 'Task Description',
                             'due_date' => '2022-07-24'
                         ]);

        
        $response_data = $response->json();
                    
        $task_id = $response_data['data']['id'];                

        $response = $this->withHeader('Authorization', 'Bearer '. $token)
                         ->deleteJson("/api/tasks/$task_id");
        
        // Assert that the response status code is 204                 
        $response->assertStatus(200);
        
        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => true,
            'message' => 'Success',
        ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task_id,
        ]);
    }

    /**
     * User can only delete tasks they created
     */
    public function test_user_cannot_delete_other_user_task(): void
    {
        $this->actingAs($this->user, 'api');

        $token = JWTAuth::fromUser($this->user);

        $task = Task::factory()->create();

        //make sure the task user id is not same as the authenticated user id
        $task->user_id = (User::factory()->create())->id;
        $task->save();
        
        $response = $this->withHeader('Authorization', 'Bearer '. $token)
                                    ->deleteJson("/api/tasks/$task->id");

        // Assert that the response status code is 403                 
        $response->assertStatus(403);
        
        // Assert that the returned response has an error messsage
        $response->assertJson([
            'status' => false,
           'message' => 'Unauthorized Action'
        ]);
    }
    
    /**
     * Unauthenticated user cannot delete a task
     */
    public function test_unauthenticated_user_cannot_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/$task->id");

        // Assert that the response status code is 401                 
        $response->assertStatus(401);
        
        // Assert that the returned response has an error messsage
        $response->assertJson([
            'status' => false,
           'message' => 'Unauthenticated.'
        ]);
    }
    
}
