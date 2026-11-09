<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['new', 'in_progress', 'done'];

        // 1. Три тестових користувачі
        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            // 2. По 2 проєкти для кожного користувача
            for ($p = 0; $p < 2; $p++) {
                $project = Project::factory()->create([
                    'user_id' => $user->id,
                ]);

                // 3. По 5 задач для кожного проєкту (статуси чергуються)
                for ($t = 0; $t < 5; $t++) {
                    $task = Task::factory()->create([
                        'project_id'  => $project->id,
                        'author_id'   => $user->id,
                        'assigned_to' => $users->random()->id,
                        'status'      => $statuses[$t % count($statuses)],
                    ]);

                    // 4. По 2-3 коментарі від різних користувачів
                    $commentAuthors = $users->random(rand(2, 3));

                    foreach ($commentAuthors as $author) {
                        Comment::factory()->create([
                            'task_id' => $task->id,
                            'user_id' => $author->id,
                        ]);
                    }
                }
            }
        }
    }
}
