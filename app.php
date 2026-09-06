<?php

declare(strict_types=1);

/**
 * The Code Munk - Front Controller
 *
 * The public marketing site is served as static HTML (the original design).
 * This controller powers the dynamic application: auth, admin & student
 * dashboards, the JSON API, lead/WhatsApp capture and public portfolios.
 */

use TCM\Core\Csrf;
use TCM\Core\Request;
use TCM\Core\Response;
use TCM\Core\Router;
use TCM\Core\View;

require __DIR__ . '/src/bootstrap.php';

// CSRF protection for state-changing web requests.
// JSON/AJAX requests from the frontend modal are exempt (same-origin fetch).
if (!str_starts_with(Request::path(), '/api') && !Request::isJson()) {
    Csrf::check();
}

$router = new Router();

$router->get('/health', static fn () => Response::success(['time' => date('c')], 'OK'));

// --------------------------------------------------------------------- //
// Authentication
// --------------------------------------------------------------------- //
$router->get('/auth/login', ['TCM\Controllers\AuthController', 'showLogin']);
$router->post('/auth/login', ['TCM\Controllers\AuthController', 'login']);
$router->get('/auth/register', ['TCM\Controllers\AuthController', 'showRegister']);
$router->post('/auth/register', ['TCM\Controllers\AuthController', 'register']);
$router->post('/auth/logout', ['TCM\Controllers\AuthController', 'logout']);
$router->post('/auth/otp/request', ['TCM\Controllers\AuthController', 'otpRequest']);
$router->post('/auth/otp/verify', ['TCM\Controllers\AuthController', 'otpVerify']);
$router->post('/auth/password/request', ['TCM\Controllers\AuthController', 'passwordOtpRequest']);
$router->post('/auth/password/reset', ['TCM\Controllers\AuthController', 'passwordReset']);

// Google OAuth
$router->get('/auth/google', ['TCM\Controllers\GoogleAuthController', 'redirect']);
$router->get('/auth/google/callback', ['TCM\Controllers\GoogleAuthController', 'callback']);

// --------------------------------------------------------------------- //
// Admin dashboard
// --------------------------------------------------------------------- //
$router->get('/admin', ['TCM\Controllers\Admin\DashboardController', 'index']);
$router->post('/admin/orders/{id}/delete', ['TCM\Controllers\Admin\DashboardController', 'deleteOrder']);
$router->post('/admin/orders/clear',       ['TCM\Controllers\Admin\DashboardController', 'clearOrders']);
$router->post('/admin/events/{id}/reset',  ['TCM\Controllers\Admin\DashboardController', 'resetEvent']);

$router->get('/admin/courses', ['TCM\Controllers\Admin\CourseController', 'index']);
$router->get('/admin/courses/create', ['TCM\Controllers\Admin\CourseController', 'create']);
$router->post('/admin/courses', ['TCM\Controllers\Admin\CourseController', 'store']);
$router->get('/admin/courses/{id}/edit', ['TCM\Controllers\Admin\CourseController', 'edit']);
$router->post('/admin/courses/{id}', ['TCM\Controllers\Admin\CourseController', 'update']);
$router->post('/admin/courses/{id}/delete', ['TCM\Controllers\Admin\CourseController', 'destroy']);
$router->post('/admin/courses/{id}/modules', ['TCM\Controllers\Admin\CourseController', 'addModule']);
$router->post('/admin/modules/{moduleId}/delete', ['TCM\Controllers\Admin\CourseController', 'deleteModule']);
$router->post('/admin/modules/{moduleId}/lessons', ['TCM\Controllers\Admin\CourseController', 'addLesson']);
$router->post('/admin/lessons/{lessonId}/delete', ['TCM\Controllers\Admin\CourseController', 'deleteLesson']);

$router->get('/admin/events', ['TCM\Controllers\Admin\EventController', 'index']);
$router->get('/admin/events/create', ['TCM\Controllers\Admin\EventController', 'create']);
$router->post('/admin/events', ['TCM\Controllers\Admin\EventController', 'store']);
$router->get('/admin/events/{id}/edit', ['TCM\Controllers\Admin\EventController', 'edit']);
$router->post('/admin/events/{id}', ['TCM\Controllers\Admin\EventController', 'update']);
$router->post('/admin/events/{id}/delete', ['TCM\Controllers\Admin\EventController', 'destroy']);

$router->get('/admin/programs', ['TCM\Controllers\Admin\ProgramController', 'index']);
$router->get('/admin/programs/create', ['TCM\Controllers\Admin\ProgramController', 'create']);
$router->post('/admin/programs', ['TCM\Controllers\Admin\ProgramController', 'store']);
$router->get('/admin/programs/{id}/edit', ['TCM\Controllers\Admin\ProgramController', 'edit']);
$router->post('/admin/programs/{id}', ['TCM\Controllers\Admin\ProgramController', 'update']);
$router->post('/admin/programs/{id}/delete', ['TCM\Controllers\Admin\ProgramController', 'destroy']);
$router->post('/admin/programs/{id}/sessions', ['TCM\Controllers\Admin\ProgramController', 'addSession']);
$router->post('/admin/sessions/{sessionId}', ['TCM\Controllers\Admin\ProgramController', 'updateSession']);
$router->post('/admin/sessions/{sessionId}/delete', ['TCM\Controllers\Admin\ProgramController', 'deleteSession']);

$router->get('/admin/internships', ['TCM\Controllers\Admin\InternshipController', 'index']);
$router->get('/admin/internships/{id}', ['TCM\Controllers\Admin\InternshipController', 'show']);
$router->post('/admin/internships/{id}', ['TCM\Controllers\Admin\InternshipController', 'update']);
$router->get('/admin/internships/{id}/resume', ['TCM\Controllers\Admin\InternshipController', 'downloadResume']);

$router->get('/admin/leads', ['TCM\Controllers\Admin\LeadController', 'index']);
$router->post('/admin/leads/{id}/status', ['TCM\Controllers\Admin\LeadController', 'updateStatus']);
$router->post('/admin/leads/{id}/convert', ['TCM\Controllers\Admin\LeadController', 'convert']);
$router->post('/admin/leads/{id}/delete', ['TCM\Controllers\Admin\LeadController', 'destroy']);

$router->get('/admin/posts', ['TCM\Controllers\Admin\PostController', 'index']);
$router->get('/admin/posts/create', ['TCM\Controllers\Admin\PostController', 'create']);
$router->post('/admin/posts', ['TCM\Controllers\Admin\PostController', 'store']);
$router->get('/admin/posts/{id}/edit', ['TCM\Controllers\Admin\PostController', 'edit']);
$router->post('/admin/posts/{id}', ['TCM\Controllers\Admin\PostController', 'update']);
$router->post('/admin/posts/{id}/delete', ['TCM\Controllers\Admin\PostController', 'destroy']);

$router->get('/admin/students', ['TCM\Controllers\Admin\StudentController', 'index']);
$router->get('/admin/students/{id}', ['TCM\Controllers\Admin\StudentController', 'show']);
$router->post('/admin/students/{id}/toggle', ['TCM\Controllers\Admin\StudentController', 'toggleStatus']);

$router->get('/admin/categories', ['TCM\Controllers\Admin\ContentController', 'categories']);
$router->post('/admin/categories', ['TCM\Controllers\Admin\ContentController', 'storeCategory']);
$router->post('/admin/categories/{id}/delete', ['TCM\Controllers\Admin\ContentController', 'deleteCategory']);

$router->get('/admin/testimonials', ['TCM\Controllers\Admin\ContentController', 'testimonials']);
$router->post('/admin/testimonials', ['TCM\Controllers\Admin\ContentController', 'storeTestimonial']);
$router->post('/admin/testimonials/{id}/delete', ['TCM\Controllers\Admin\ContentController', 'deleteTestimonial']);

$router->get('/admin/messages', ['TCM\Controllers\Admin\ContentController', 'messages']);
$router->post('/admin/messages/{id}/delete', ['TCM\Controllers\Admin\ContentController', 'deleteMessage']);

$router->get('/admin/settings', ['TCM\Controllers\Admin\ContentController', 'settings']);
$router->post('/admin/settings', ['TCM\Controllers\Admin\ContentController', 'saveSettings']);

// Live class links
$router->get('/admin/live-class',           ['TCM\Controllers\Admin\LiveClassController', 'index']);
$router->post('/admin/live-class/send',     ['TCM\Controllers\Admin\LiveClassController', 'send']);
$router->post('/admin/live-class/{id}/delete', ['TCM\Controllers\Admin\LiveClassController', 'delete']);

// Admin Wallet & Withdrawals
$router->get('/admin/wallet',               ['TCM\Controllers\Admin\WalletController', 'index']);
$router->post('/admin/wallet/{id}/approve', ['TCM\Controllers\Admin\WalletController', 'approveWithdrawal']);
$router->post('/admin/wallet/{id}/reject',  ['TCM\Controllers\Admin\WalletController', 'rejectWithdrawal']);

// Lead Forms
$router->get('/admin/lead-forms', ['TCM\Controllers\Admin\LeadFormController', 'index']);
$router->get('/admin/lead-forms/create', ['TCM\Controllers\Admin\LeadFormController', 'create']);
$router->post('/admin/lead-forms', ['TCM\Controllers\Admin\LeadFormController', 'store']);
$router->get('/admin/lead-forms/{id}/edit', ['TCM\Controllers\Admin\LeadFormController', 'edit']);
$router->post('/admin/lead-forms/{id}', ['TCM\Controllers\Admin\LeadFormController', 'update']);
$router->post('/admin/lead-forms/{id}/delete', ['TCM\Controllers\Admin\LeadFormController', 'destroy']);

// Public lead form pages
$router->get('/form/{slug}', ['TCM\Controllers\Admin\LeadFormController', 'show']);
$router->post('/form/{slug}/submit', ['TCM\Controllers\Admin\LeadFormController', 'submit']);
$router->get('/form/{slug}/thank-you', ['TCM\Controllers\Admin\LeadFormController', 'thankYou']);

// --------------------------------------------------------------------- //
// Student dashboard
// --------------------------------------------------------------------- //
$router->get('/student', ['TCM\Controllers\Student\DashboardController', 'index']);
$router->get('/student/onboarding', ['TCM\Controllers\AuthController', 'showOnboarding']);
$router->post('/student/onboarding', ['TCM\Controllers\AuthController', 'saveOnboarding']);

$router->get('/student/profile', ['TCM\Controllers\Student\ProfileController', 'edit']);
$router->post('/student/profile', ['TCM\Controllers\Student\ProfileController', 'update']);
$router->post('/student/profile/password', ['TCM\Controllers\Student\ProfileController', 'changePassword']);

$router->get('/student/courses', ['TCM\Controllers\Student\CourseController', 'browse']);
$router->get('/student/courses/{slug}', ['TCM\Controllers\Student\CourseController', 'show']);
$router->post('/student/courses/{id}/buy', ['TCM\Controllers\Student\CourseController', 'purchase']);
$router->get('/student/learn/{id}', ['TCM\Controllers\Student\CourseController', 'learn']);
$router->get('/student/learn/{id}/lessons', ['TCM\Controllers\Student\CourseController', 'getLessons']);
$router->post('/student/learn/{id}/lessons/{lessonId}', ['TCM\Controllers\Student\CourseController', 'toggleLesson']);
$router->get('/student/lessons/{lessonId}/content', ['TCM\Controllers\Student\CourseController', 'getLessonContent']);

// Live Classes
$router->get('/student/live-classes', ['TCM\Controllers\Student\LiveClassController', 'scheduled']);

$router->get('/student/events', ['TCM\Controllers\Student\EventController', 'browse']);
$router->post('/student/events/{id}/join', ['TCM\Controllers\Student\EventController', 'join']);

$router->get('/student/programs', ['TCM\Controllers\Student\ProgramController', 'browse']);
$router->get('/student/programs/{slug}', ['TCM\Controllers\Student\ProgramController', 'show']);
$router->post('/student/programs/{id}/enquire', ['TCM\Controllers\Student\ProgramController', 'enquire']);

$router->get('/student/applications', ['TCM\Controllers\Student\InternshipController', 'applications']);
$router->get('/student/internships/{id}/apply', ['TCM\Controllers\Student\InternshipController', 'showForm']);
$router->post('/student/internships/{id}/apply', ['TCM\Controllers\Student\InternshipController', 'apply']);

$router->get('/student/portfolio', ['TCM\Controllers\Student\PortfolioController', 'index']);
$router->post('/student/portfolio/projects', ['TCM\Controllers\Student\PortfolioController', 'storeProject']);
$router->post('/student/portfolio/projects/{id}/delete', ['TCM\Controllers\Student\PortfolioController', 'deleteProject']);
$router->post('/student/portfolio/skills', ['TCM\Controllers\Student\PortfolioController', 'storeSkill']);
$router->post('/student/portfolio/skills/{id}/delete', ['TCM\Controllers\Student\PortfolioController', 'deleteSkill']);
$router->post('/student/portfolio/achievements', ['TCM\Controllers\Student\PortfolioController', 'storeAchievement']);
$router->post('/student/portfolio/achievements/{id}/delete', ['TCM\Controllers\Student\PortfolioController', 'deleteAchievement']);
$router->post('/student/portfolio/banner', ['TCM\Controllers\Student\PortfolioController', 'uploadBanner']);
$router->post('/student/portfolio/banner/remove', ['TCM\Controllers\Student\PortfolioController', 'removeBanner']);

// Public, shareable portfolio
$router->get('/portfolio/{id}',          ['TCM\Controllers\Student\PortfolioController', 'publicView']);
$router->get('/portfolio/{id}/og-image', ['TCM\Controllers\Student\PortfolioController', 'ogImage']);

// Student Community
$router->get('/student/community',      ['TCM\Controllers\Student\CommunityController', 'browse']);
$router->get('/student/community/{id}', ['TCM\Controllers\Student\CommunityController', 'profile']);

// Student Chat & Help
$router->get('/student/chat',                                   ['TCM\Controllers\Student\HelpController', 'chatPage']);
$router->post('/student/help/request/{id}',                     ['TCM\Controllers\Student\HelpController', 'sendRequest']);
$router->post('/student/help/requests/{id}/accept',             ['TCM\Controllers\Student\HelpController', 'acceptRequest']);
$router->post('/student/help/requests/{id}/decline',            ['TCM\Controllers\Student\HelpController', 'declineRequest']);
$router->post('/student/help/requests/{id}/cookie',             ['TCM\Controllers\Student\HelpController', 'giveCookie']);
$router->post('/student/groups/create',                         ['TCM\Controllers\Student\HelpController', 'createGroup']);
$router->post('/student/groups/{id}/leave',                     ['TCM\Controllers\Student\HelpController', 'leaveGroup']);
$router->get('/api/student/chat/groups',                        ['TCM\Controllers\Student\HelpController', 'apiGroups']);
$router->get('/api/student/chat/groups/{id}/messages',          ['TCM\Controllers\Student\HelpController', 'apiMessages']);
$router->post('/api/student/chat/groups/{id}/messages',         ['TCM\Controllers\Student\HelpController', 'apiSendMessage']);
$router->get('/api/student/help/requests',                      ['TCM\Controllers\Student\HelpController', 'apiRequests']);
$router->get('/api/student/search',                             ['TCM\Controllers\Student\HelpController', 'apiSearchStudents']);

// Student Wallet
$router->get('/student/wallet',                    ['TCM\Controllers\Student\WalletController', 'index']);
$router->post('/student/wallet/withdraw',          ['TCM\Controllers\Student\WalletController', 'requestWithdrawal']);

// Student TCM Agent (AI Assistant)
$router->get('/student/agent',                     ['TCM\Controllers\Student\AgentController', 'index']);
$router->post('/student/agent/chat',               ['TCM\Controllers\Student\AgentController', 'chat']);

// Student Daily Tasks (AI-generated)
$router->get('/student/tasks',                     ['TCM\Controllers\Student\TaskController', 'index']);
$router->get('/student/tasks/{id}',                ['TCM\Controllers\Student\TaskController', 'show']);
$router->post('/student/tasks/{id}/status',        ['TCM\Controllers\Student\TaskController', 'updateStatus']);
$router->get('/student/tasks/generate',            ['TCM\Controllers\Student\TaskController', 'generate']);

// Student Course Notes (W3Schools style)
$router->get('/student/notes/{id}',                ['TCM\Controllers\Student\NoteController', 'index']);
$router->get('/student/notes/{id}/{slug}',         ['TCM\Controllers\Student\NoteController', 'show']);

// --------------------------------------------------------------------- //
// Student Payments
// --------------------------------------------------------------------- //
$router->get('/student/payments', ['TCM\Controllers\Student\PaymentController', 'history']);
$router->get('/student/payments/submit', ['TCM\Controllers\Student\PaymentController', 'showForm']);
$router->post('/student/payments/submit', ['TCM\Controllers\Student\PaymentController', 'store']);
$router->get('/student/payments/{id}/receipt', ['TCM\Controllers\Student\PaymentController', 'receipt']);
$router->post('/student/payments/{id}/screenshot', ['TCM\Controllers\Student\PaymentController', 'updateScreenshot']);
$router->post('/student/interest', ['TCM\Controllers\Student\PaymentController', 'submitInterest']);

// --------------------------------------------------------------------- //
// Admin Payments
// --------------------------------------------------------------------- //
$router->get('/admin/payments', ['TCM\Controllers\Admin\PaymentController', 'index']);
$router->get('/admin/payments/{id}', ['TCM\Controllers\Admin\PaymentController', 'show']);
$router->post('/admin/payments/{id}/approve', ['TCM\Controllers\Admin\PaymentController', 'approve']);
$router->post('/admin/payments/{id}/reject', ['TCM\Controllers\Admin\PaymentController', 'reject']);
$router->get('/admin/payments/{id}/screenshot', ['TCM\Controllers\Admin\PaymentController', 'viewScreenshot']);

// Lead capture + WhatsApp hand-off (used by the static site CTAs and dashboards)
$router->post('/enquiry', ['TCM\Controllers\EnquiryController', 'store']);

// --------------------------------------------------------------------- //
// Public JSON API (consumed by the static marketing site via tcm-app.js)
// --------------------------------------------------------------------- //
$router->get('/api/me', ['TCM\Controllers\Api\PublicController', 'me']);
$router->get('/api/courses', ['TCM\Controllers\Api\PublicController', 'courses']);
$router->get('/api/courses/{slug}', ['TCM\Controllers\Api\PublicController', 'course']);
$router->get('/api/events', ['TCM\Controllers\Api\PublicController', 'events']);
$router->get('/api/events/{slug}', ['TCM\Controllers\Api\PublicController', 'event']);
$router->get('/api/programs', ['TCM\Controllers\Api\PublicController', 'programs']);
$router->get('/api/posts', ['TCM\Controllers\Api\PublicController', 'posts']);
$router->get('/api/testimonials', ['TCM\Controllers\Api\PublicController', 'testimonials']);
$router->post('/api/contact', ['TCM\Controllers\Api\PublicController', 'contact']);
$router->post('/api/subscribe', ['TCM\Controllers\Api\PublicController', 'subscribe']);

// Push notification token registration
$router->post('/api/notifications/token',       ['TCM\Controllers\Api\NotificationController', 'saveToken']);
$router->get('/api/notifications',              ['TCM\Controllers\Api\NotificationController', 'index']);
$router->post('/api/notifications/read-all',    ['TCM\Controllers\Api\NotificationController', 'readAll']);
$router->post('/api/notifications/{id}/read',   ['TCM\Controllers\Api\NotificationController', 'readOne']);
$router->post('/api/notifications/send',        ['TCM\Controllers\Api\NotificationController', 'send']);

// AI curriculum generator (admin only)
$router->post('/api/ai/curriculum',                    ['TCM\Controllers\Api\AIController', 'curriculum']);
$router->post('/admin/courses/{id}/ai-curriculum',     ['TCM\Controllers\Admin\CourseController', 'regenerateCurriculum']);

// --------------------------------------------------------------------- //
// AI Content Generator (admin & student)
// --------------------------------------------------------------------- //
// Admin routes
$router->get('/admin/ai-content',                          ['TCM\Controllers\Admin\AIContentController', 'index']);
$router->get('/admin/ai-content/status',                   ['TCM\Controllers\Admin\AIContentController', 'getContentStatus']);
$router->get('/admin/ai-content/lesson/{id}',              ['TCM\Controllers\Admin\AIContentController', 'getLesson']);
$router->post('/admin/ai-content/generate-lesson',         ['TCM\Controllers\Admin\AIContentController', 'generateLesson']);
$router->post('/admin/ai-content/generate-module',         ['TCM\Controllers\Admin\AIContentController', 'generateModule']);
$router->post('/admin/ai-content/regenerate-lesson',       ['TCM\Controllers\Admin\AIContentController', 'regenerateLesson']);
$router->post('/admin/ai-content/publish',                 ['TCM\Controllers\Admin\AIContentController', 'publishContent']);

// Student routes for lesson content
$router->get('/student/lesson-content/{id}',               ['TCM\Controllers\Student\LessonContentController', 'getContent']);
$router->post('/student/lesson-content/submit-exercise',   ['TCM\Controllers\Student\LessonContentController', 'submitExercise']);
$router->get('/student/lesson-content/my-submissions/{id}',['TCM\Controllers\Student\LessonContentController', 'getMySubmissions']);

// On-demand lesson concepts generation for students
$router->get('/student/lesson-concepts/{id}',              ['TCM\Controllers\Student\LessonConceptsController', 'getConcepts']);

// --------------------------------------------------------------------- //
// Dispatch
// --------------------------------------------------------------------- //
try {
    $router->dispatch(Request::method(), Request::path());
} catch (\Throwable $e) {
    http_response_code(500);
    if (Request::isJson()) {
        Response::error(config('app.debug') ? $e->getMessage() : 'Server error.', 500);
    }
    if (config('app.debug')) {
        echo '<pre style="padding:2rem;font-family:monospace;">';
        echo 'Error: ' . e($e->getMessage()) . "\n\n" . e($e->getTraceAsString());
        echo '</pre>';
    } else {
        View::render('errors/500', [], 'public');
    }
}
