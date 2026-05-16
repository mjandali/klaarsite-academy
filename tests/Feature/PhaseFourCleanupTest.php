<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PhaseFourCleanupTest extends TestCase
{
    public function test_migrate_fresh_seed_completes_successfully_after_migration_cleanup(): void
    {
        $databasePath = database_path('phase-four-schema-test.sqlite');

        if (file_exists($databasePath)) {
            unlink($databasePath);
        }

        touch($databasePath);

        config()->set('database.connections.phase_four_sqlite', array_merge(
            config('database.connections.sqlite'),
            ['database' => $databasePath]
        ));

        try {
            $exitCode = Artisan::call('migrate:fresh', [
                '--database' => 'phase_four_sqlite',
                '--seed' => true,
                '--force' => true,
            ]);

            $this->assertSame(0, $exitCode);
            $this->assertTrue(Schema::connection('phase_four_sqlite')->hasTable('users'));
            $this->assertTrue(Schema::connection('phase_four_sqlite')->hasTable('courses'));
            $this->assertTrue(Schema::connection('phase_four_sqlite')->hasTable('lessons'));
            $this->assertTrue(Schema::connection('phase_four_sqlite')->hasTable('lesson_media'));
            $this->assertTrue(Schema::connection('phase_four_sqlite')->hasTable('page_views'));
            $this->assertTrue(Schema::connection('phase_four_sqlite')->hasTable('utm_campaigns'));
        } finally {
            DB::disconnect('phase_four_sqlite');
            DB::purge('phase_four_sqlite');

            if (file_exists($databasePath)) {
                unlink($databasePath);
            }
        }
    }

    public function test_legacy_is_published_columns_do_not_exist(): void
    {
        $this->assertFalse(Schema::hasColumn('courses', 'is_published'));
        $this->assertFalse(Schema::hasColumn('lessons', 'is_published'));
    }

    public function test_browser_confirm_calls_are_removed_from_key_admin_pages(): void
    {
        $courseIndex = file_get_contents(resource_path('js/Pages/Admin/Courses/Index.vue'));
        $courseEdit = file_get_contents(resource_path('js/Pages/Admin/Courses/Edit.vue'));

        $this->assertIsString($courseIndex);
        $this->assertIsString($courseEdit);
        $this->assertStringNotContainsString('confirm(', $courseIndex);
        $this->assertStringNotContainsString('confirm(', $courseEdit);
        $this->assertStringContainsString('useConfirm', $courseIndex);
        $this->assertStringContainsString('useConfirm', $courseEdit);
    }

    public function test_toast_flash_messages_are_shared_and_layouts_render_the_toaster(): void
    {
        $this->withSession([
            'success' => 'Saved successfully',
            'error' => 'Something went wrong',
        ])
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('flash.success', 'Saved successfully')
                ->where('flash.error', 'Something went wrong')
            );

        $studentLayout = file_get_contents(resource_path('js/Layouts/StudentLayout.vue'));
        $adminLayout = file_get_contents(resource_path('js/Layouts/AdminLayout.vue'));
        $publicLayout = file_get_contents(resource_path('js/Layouts/PublicLayout.vue'));
        $toasterComponent = file_get_contents(resource_path('js/Components/AppFlashToaster.vue'));

        $this->assertIsString($studentLayout);
        $this->assertIsString($adminLayout);
        $this->assertIsString($publicLayout);
        $this->assertIsString($toasterComponent);
        $this->assertStringContainsString('<AppFlashToaster />', $studentLayout);
        $this->assertStringContainsString('<AppFlashToaster />', $adminLayout);
        $this->assertStringContainsString('<AppFlashToaster />', $publicLayout);
        $this->assertStringContainsString('data-toast-flash="true"', $toasterComponent);
    }

    public function test_learning_sidebar_and_mobile_toggle_exist_on_learning_pages(): void
    {
        $learningShell = file_get_contents(resource_path('js/Components/LearningShell.vue'));
        $coursePage = file_get_contents(resource_path('js/Pages/Student/Learn/Course.vue'));
        $lessonPage = file_get_contents(resource_path('js/Pages/Student/Learn/Lesson.vue'));

        $this->assertIsString($learningShell);
        $this->assertIsString($coursePage);
        $this->assertIsString($lessonPage);
        $this->assertStringContainsString('data-learning-sidebar="desktop"', $learningShell);
        $this->assertStringContainsString('data-learning-sidebar="drawer"', $learningShell);
        $this->assertStringContainsString('data-learning-toggle="mobile"', $learningShell);
        $this->assertStringContainsString('LearningShell', $coursePage);
        $this->assertStringContainsString('LearningShell', $lessonPage);
    }

    public function test_student_navigation_contains_profile_and_my_courses_links(): void
    {
        $studentLayout = file_get_contents(resource_path('js/Layouts/StudentLayout.vue'));
        $publicLayout = file_get_contents(resource_path('js/Layouts/PublicLayout.vue'));

        $this->assertIsString($studentLayout);
        $this->assertIsString($publicLayout);
        $this->assertStringContainsString('/dashboard/my-courses', $studentLayout);
        $this->assertStringContainsString('/dashboard/profile', $studentLayout);
        $this->assertStringContainsString('/dashboard/my-courses', $publicLayout);
        $this->assertStringContainsString('/dashboard/profile', $publicLayout);
    }
}
