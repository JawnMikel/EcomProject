<?php

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Views\TwigExtension;
use Twig\TwigFunction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use RedBeanPHP\R;
use App\Utilities\I18n;
use App\Utilities\Auth;
use App\Utilities\FlashMessage;

// Start session
session_start();

// Load Composer's autoloader
require __DIR__ . '/../vendor/autoload.php';

// Setup RedBeanPHP ORM with SQLite database
R::setup('sqlite:' . __DIR__ . '/../db/shop.sqlite');

// Freeze for production (uncomment when deploying)
// R::freeze(true);

// Create the Slim application
$app = AppFactory::create();

// Set the base path
$app->setBasePath('/GAINZ/public');

// Setup Twig templating engine
$twig = Twig::create(__DIR__ . '/../templates', [
    'cache' => false, // Disable cache in development
    'auto_reload' => true,
    'debug' => true
]);

$routeParser = $app->getRouteCollector()->getRouteParser();

// Add Slim Twig Extension for routing functions (url_for, full_url_for, base_path)
$twig->addExtension(new TwigExtension($routeParser, $app->getBasePath()));

// Add legacy path() alias for existing templates
$twig->getEnvironment()->addFunction(new TwigFunction('path', function (string $name, array $data = [], array $queryParams = []) use ($routeParser): string {
    return $routeParser->urlFor($name, $data, $queryParams);
}));

$twig->getEnvironment()->addFunction(new TwigFunction('path_for', function (string $name, array $data = [], array $queryParams = []) use ($routeParser): string {
    return $routeParser->urlFor($name, $data, $queryParams);
}));

// ──────────────────────────────────────────────
//  CUSTOM MIDDLEWARE
// ──────────────────────────────────────────────
// Note: Middleware is added BEFORE other middleware so it executes AFTER them
// (Slim uses LIFO - Last In, First Out execution order)

// Locale middleware - detect and set language
$localeMiddleware = function (Request $request, RequestHandlerInterface $next) {
    // Check session for saved locale
    $locale = $_SESSION['locale'] ?? null;
    
    if (!$locale) {
        // Try to detect from Accept-Language header
        $acceptLanguage = $request->getHeaderLine('Accept-Language');
        $i18n = I18n::getInstance();
        $locale = $i18n->detectLocaleFromHeader($acceptLanguage);
    }
    
    $i18n = I18n::getInstance();
    $i18n->setLocale($locale);
    
    $_SESSION['locale'] = $locale;
    
    return $next->handle($request);
};

// Auth middleware - make user available to all templates
$authMiddleware = function (Request $request, RequestHandlerInterface $next) {
    $twig = Twig::fromRequest($request);
    $view = $twig->getEnvironment();
    
    // Make auth status available to all templates
    $view->addGlobal('isLoggedIn', Auth::isLoggedIn());
    $view->addGlobal('currentUser', Auth::getCurrentUser());
    $view->addGlobal('isAdmin', Auth::isAdmin());
    $view->addGlobal('flashMessages', FlashMessage::getAndClear());
    $view->addGlobal('locale', I18n::getInstance()->getLocale());
    $view->addGlobal('i18n', I18n::getInstance());
    
    return $next->handle($request);
};

// Add custom middleware first (so they execute last, after TwigMiddleware)
$app->add($localeMiddleware);
$app->add($authMiddleware);

// Add Twig middleware (executes first due to LIFO order)
$app->add(TwigMiddleware::create($app, $twig));

// Add body parsing middleware
$app->addBodyParsingMiddleware();

// Add routing middleware
$app->addRoutingMiddleware();

// Add error middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// ──────────────────────────────────────────────
//  ROUTES
// ──────────────────────────────────────────────

// Home page
$app->get('/', function (Request $request, Response $response) {
    $twig = Twig::fromRequest($request);
    return $twig->render($response, 'home.html.twig');
})->setName('home');

// Locale switcher
$app->get('/locale/{locale}', function (Request $request, Response $response, array $args) {
    $locale = $args['locale'];
    $i18n = I18n::getInstance();
    
    if ($i18n->setLocale($locale)) {
        $_SESSION['locale'] = $locale;
    }
    
    $referer = $request->getHeaderLine('Referer') ?: '/';
    return $response->withHeader('Location', $referer)->withStatus(302);
})->setName('locale');

// Seed database route
$app->get('/seed', function (Request $request, Response $response) {
    // Read and execute seed SQL
    $schemaSql = file_get_contents(__DIR__ . '/../db/schema.sql');
    $seedSql = file_get_contents(__DIR__ . '/../db/seed.sql');
    
    try {
        R::exec($schemaSql);
        R::exec($seedSql);
        $response->getBody()->write('<h1>Database seeded successfully!</h1><p><a href="/">Go to Home</a></p>');
    } catch (\Exception $e) {
        $response->getBody()->write('<h1>Error seeding database</h1><p>' . $e->getMessage() . '</p>');
    }
    
    return $response;
});

// ─── Auth Routes ───
$app->get('/login', function (Request $request, Response $response) {
    $twig = Twig::fromRequest($request);
    return $twig->render($response, 'auth/login.html.twig');
})->setName('login');

$app->post('/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    $result = Auth::attemptLogin($email, $password);
    
    if ($result['success']) {
        FlashMessage::success('auth.login_success');
        return $response->withHeader('Location', '/GAINZ/public/')->withStatus(302);
    } elseif ($result['requires2fa']) {
        return $response->withHeader('Location', '/GAINZ/public/2fa')->withStatus(302);
    } else {
        FlashMessage::error('auth.invalid_credentials');
        return $response->withHeader('Location', '/GAINZ/public/login')->withStatus(302);
    }
});

$app->get('/2fa', function (Request $request, Response $response) {
    $twig = Twig::fromRequest($request);
    return $twig->render($response, 'auth/2fa.html.twig');
})->setName('2fa');

$app->post('/2fa', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $code = $data['code'] ?? '';
    
    if (Auth::verify2FACode($code)) {
        FlashMessage::success('auth.login_success');
        return $response->withHeader('Location', '/GAINZ/public/')->withStatus(302);
    }
    
    FlashMessage::error('auth.invalid_2fa_code');
    return $response->withHeader('Location', '/GAINZ/public/2fa')->withStatus(302);
});

$app->get('/register', function (Request $request, Response $response) {
    $twig = Twig::fromRequest($request);
    return $twig->render($response, 'auth/register.html.twig');
})->setName('register');

$app->post('/register', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $result = Auth::registerUser($data);
    
    if ($result['success']) {
        FlashMessage::success('auth.register_success');
        return $response->withHeader('Location', '/GAINZ/public/login')->withStatus(302);
    }
    
    foreach ($result['errors'] as $error) {
        FlashMessage::error($error);
    }
    
    return $response->withHeader('Location', '/GAINZ/public/register')->withStatus(302);
});

$app->get('/logout', function (Request $request, Response $response) {
    Auth::logout();
    FlashMessage::info('auth.logout_success');
    return $response->withHeader('Location', '/GAINZ/public/')->withStatus(302);
})->setName('logout');

// ─── Exercise Routes ───
$app->get('/exercises', function (Request $request, Response $response) {
    $twig = Twig::fromRequest($request);
    $i18n = I18n::getInstance();
    $locale = $i18n->getLocale();
    
    // Get filter parameters
    $category = $request->getQueryParam('category');
    $difficulty = $request->getQueryParam('difficulty');
    $search = $request->getQueryParam('search');
    
    // Build query
    $where = ['1'];
    $bindings = [];
    
    if ($category) {
        $where[] = 'category_id = ?';
        $bindings[] = $category;
    }
    
    if ($difficulty) {
        $where[] = 'difficulty = ?';
        $bindings[] = $difficulty;
    }
    
    if ($search) {
        $where[] = '(name_en LIKE ? OR name_fr LIKE ?)';
        $bindings[] = "%$search%";
        $bindings[] = "%$search%";
    }
    
    $exercises = R::findAll('exercise', implode(' AND ', $where), $bindings);
    $categories = R::findAll('category');
    
    return $twig->render($response, 'exercises/index.html.twig', [
        'exercises' => array_map(fn($b) => \App\Models\Exercise::fromBean($b), $exercises),
        'categories' => array_map(fn($b) => \App\Models\Category::fromBean($b), $categories),
        'currentCategory' => $category,
        'currentDifficulty' => $difficulty,
        'search' => $search
    ]);
})->setName('exercises');

$app->get('/exercises/{id}', function (Request $request, Response $response, array $args) {
    $twig = Twig::fromRequest($request);
    $id = (int) $args['id'];
    
    $bean = R::load('exercise', $id);
    if (!$bean->id) {
        FlashMessage::error('exercises.not_found');
        return $response->withHeader('Location', '/GAINZ/public/exercises')->withStatus(302);
    }
    
    $exercise = \App\Models\Exercise::fromBean($bean);
    return $twig->render($response, 'exercises/show.html.twig', ['exercise' => $exercise]);
})->setName('exercises.show');

// API endpoint for live search
$app->get('/api/exercises/search', function (Request $request, Response $response) {
    $query = $request->getQueryParam('q', '');
    $category = $request->getQueryParam('category', '');
    $i18n = I18n::getInstance();
    $locale = $i18n->getLocale();
    
    $where = ['is_active = 1'];
    $bindings = [];
    
    if ($query) {
        $where[] = '(name_en LIKE ? OR name_fr LIKE ?)';
        $bindings[] = "%$query%";
        $bindings[] = "%$query%";
    }
    
    if ($category) {
        $where[] = 'category_id = ?';
        $bindings[] = $category;
    }
    
    $exercises = R::findAll('exercise', implode(' AND ', $where), $bindings);
    
    $data = [];
    foreach ($exercises as $bean) {
        $exercise = \App\Models\Exercise::fromBean($bean);
        $data[] = [
            'id' => $exercise->id,
            'name' => $exercise->getName($locale),
            'difficulty' => $exercise->difficulty,
            'category_id' => $exercise->categoryId
        ];
    }
    
    $response->getBody()->write(json_encode(['exercises' => $data]));
    return $response->withHeader('Content-Type', 'application/json');
})->setName('api.exercises.search');

// ─── Workout Routes ───
$app->get('/workouts', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $twig = Twig::fromRequest($request);
    $userId = Auth::getUserId();
    
    $sessions = R::findAll('workout_session', 'user_id = ? ORDER BY started_at DESC', [$userId]);
    
    return $twig->render($response, 'workouts/index.html.twig', [
        'sessions' => array_map(fn($b) => \App\Models\WorkoutSession::fromBean($b), $sessions)
    ]);
})->setName('workouts');

$app->get('/workouts/start', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $twig = Twig::fromRequest($request);
    
    $programWorkoutId = $request->getQueryParam('program_workout');
    $programWorkout = null;
    
    if ($programWorkoutId) {
        $bean = R::load('program_workouts', $programWorkoutId);
        if ($bean->id) {
            $programWorkout = \App\Models\ProgramWorkout::fromBean($bean);
        }
    }
    
    return $twig->render($response, 'workouts/start.html.twig', ['programWorkout' => $programWorkout]);
})->setName('workouts.start');

$app->post('/workouts/start', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $userId = Auth::getUserId();
    $data = $request->getParsedBody();
    
    $bean = R::dispense('workout_session');
    $bean->user_id = $userId;
    $bean->program_workout_id = $data['program_workout_id'] ?: null;
    $bean->started_at = date('Y-m-d H:i:s');
    
    R::store($bean);
    
    FlashMessage::success('workout.session_started');
    return $response->withHeader('Location', "/GAINZ/public/workouts/{$bean->id}")->withStatus(302);
});

$app->get('/workouts/{id}', function (Request $request, Response $response, array $args) {
    Auth::requireAuth('/login');
    $twig = Twig::fromRequest($request);
    $userId = Auth::getUserId();
    $sessionId = (int) $args['id'];
    
    $bean = R::load('workout_session', $sessionId);
    
    if (!$bean->id || $bean->user_id != $userId) {
        FlashMessage::error('workout.not_found');
        return $response->withHeader('Location', '/GAINZ/public/workouts')->withStatus(302);
    }
    
    $session = \App\Models\WorkoutSession::fromBean($bean);
    
    // Get session items
    $items = R::findAll('workout_session_item', 'session_id = ? ORDER BY exercise_id, set_number', [$sessionId]);
    $sessionItems = array_map(fn($b) => \App\Models\WorkoutSessionItem::fromBean($b), $items);
    
    return $twig->render($response, 'workouts/session.html.twig', [
        'session' => $session,
        'items' => $sessionItems
    ]);
})->setName('workouts.session');

$app->post('/workouts/{id}/add-set', function (Request $request, Response $response, array $args) {
    Auth::requireAuth('/login');
    $userId = Auth::getUserId();
    $sessionId = (int) $args['id'];
    $data = $request->getParsedBody();
    
    $bean = R::load('workout_session', $sessionId);
    if (!$bean->id || $bean->user_id != $userId) {
        return $response->withStatus(404);
    }
    
    $itemBean = R::dispense('workout_session_item');
    $itemBean->session_id = $sessionId;
    $itemBean->exercise_id = $data['exercise_id'];
    $itemBean->set_number = $data['set_number'];
    $itemBean->reps = $data['reps'];
    $itemBean->weight = $data['weight'] ?? 0;
    $itemBean->completed = 1;
    
    R::store($itemBean);
    
    FlashMessage::success('workout.set_added');
    return $response->withHeader('Location', "/GAINZ/public/workouts/{$sessionId}")->withStatus(302);
})->setName('workouts.add_set');

$app->post('/workouts/{id}/end', function (Request $request, Response $response, array $args) {
    Auth::requireAuth('/login');
    $userId = Auth::getUserId();
    $sessionId = (int) $args['id'];
    $data = $request->getParsedBody();
    
    $bean = R::load('workout_session', $sessionId);
    if (!$bean->id || $bean->user_id != $userId) {
        return $response->withStatus(404);
    }
    
    $bean->completed_at = date('Y-m-d H:i:s');
    $bean->duration_seconds = $data['duration'] ?? 0;
    $bean->notes = $data['notes'] ?? null;
    
    R::store($bean);
    
    FlashMessage::success('workout.session_ended');
    return $response->withHeader('Location', "/GAINZ/public/workouts/{$sessionId}")->withStatus(302);
})->setName('workouts.end');

// ─── Programs Routes ───
$app->get('/programs', function (Request $request, Response $response) {
    $twig = Twig::fromRequest($request);
    
    $programs = R::findAll('training_programs', 'is_public = 1');
    
    return $twig->render($response, 'programs/index.html.twig', [
        'programs' => array_map(fn($b) => \App\Models\TrainingProgram::fromBean($b), $programs)
    ]);
})->setName('programs');

$app->get('/programs/{id}', function (Request $request, Response $response, array $args) {
    $twig = Twig::fromRequest($request);
    $id = (int) $args['id'];
    
    $bean = R::load('training_programs', $id);
    if (!$bean->id) {
        FlashMessage::error('programs.not_found');
        return $response->withHeader('Location', '/GAINZ/public/programs')->withStatus(302);
    }
    
    $program = \App\Models\TrainingProgram::fromBean($bean);
    
    // Get program workouts
    $workouts = R::findAll('program_workouts', 'program_id = ? ORDER BY day_order', [$id]);
    $programWorkouts = array_map(fn($b) => \App\Models\ProgramWorkout::fromBean($b), $workouts);
    
    return $twig->render($response, 'programs/show.html.twig', [
        'program' => $program,
        'workouts' => $programWorkouts
    ]);
})->setName('programs.show');

// ─── Progress Routes ───
$app->get('/progress', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $twig = Twig::fromRequest($request);
    $userId = Auth::getUserId();
    
    // Get personal records
    $records = R::findAll('personal_records', 'user_id = ? ORDER BY weight DESC', [$userId]);
    $personalRecords = array_map(fn($b) => \App\Models\PersonalRecord::fromBean($b), $records);
    
    // Get workout stats
    $totalWorkouts = R::count('workout_session', 'user_id = ? AND completed_at IS NOT NULL', [$userId]);
    
    // Get workout calendar data (last 30 days)
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    $recentWorkouts = R::getAll(
        "SELECT DATE(started_at) as date FROM workout_session 
         WHERE user_id = ? AND started_at >= ? 
         GROUP BY DATE(started_at)",
        [$userId, $thirtyDaysAgo]
    );
    
    $workoutDates = array_column($recentWorkouts, 'date');
    
    return $twig->render($response, 'progress/index.html.twig', [
        'records' => $personalRecords,
        'totalWorkouts' => $totalWorkouts,
        'workoutDates' => $workoutDates
    ]);
})->setName('progress');

// ─── Bodyweight Routes ───
$app->get('/bodyweight', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $twig = Twig::fromRequest($request);
    $userId = Auth::getUserId();
    
    $entries = R::findAll('bodyweight_entries', 'user_id = ? ORDER BY recorded_at DESC LIMIT 50', [$userId]);
    $bodyweightEntries = array_map(fn($b) => \App\Models\BodyweightEntry::fromBean($b), $entries);
    
    // Get current weight
    $currentWeight = null;
    if (!empty($bodyweightEntries)) {
        $currentWeight = $bodyweightEntries[0];
    }
    
    return $twig->render($response, 'bodyweight/index.html.twig', [
        'entries' => $bodyweightEntries,
        'currentWeight' => $currentWeight
    ]);
})->setName('bodyweight');

$app->post('/bodyweight', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $userId = Auth::getUserId();
    $data = $request->getParsedBody();
    
    $errors = \App\Utilities\Validator::validateBodyweightEntry($data);
    if (!empty($errors)) {
        foreach ($errors as $error) {
            FlashMessage::error($error);
        }
        return $response->withHeader('Location', '/GAINZ/public/bodyweight')->withStatus(302);
    }
    
    $bean = R::dispense('bodyweight_entries');
    $bean->user_id = $userId;
    $bean->weight = $data['weight'];
    $bean->unit = $data['unit'] ?? 'kg';
    $bean->recorded_at = date('Y-m-d H:i:s');
    
    R::store($bean);
    
    FlashMessage::success('bodyweight.entry_added');
    return $response->withHeader('Location', '/GAINZ/public/bodyweight')->withStatus(302);
})->setName('bodyweight.store');

// ─── Profile Routes ───
$app->get('/profile', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $twig = Twig::fromRequest($request);
    $user = Auth::getCurrentUser();
    
    return $twig->render($response, 'profile/index.html.twig', ['user' => $user]);
})->setName('profile');

$app->get('/profile/2fa', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $twig = Twig::fromRequest($request);
    $user = Auth::getCurrentUser();
    
    $qrCodeUrl = null;
    if (!$user->twoFactorEnabled) {
        $secretData = Auth::generate2FASecret($user);
        $qrCodeUrl = $secretData['qrCodeUrl'];
    }
    
    return $twig->render($response, 'profile/2fa.html.twig', [
        'user' => $user,
        'qrCodeUrl' => $qrCodeUrl
    ]);
})->setName('profile.2fa');

$app->post('/profile/2fa/enable', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $user = Auth::getCurrentUser();
    $data = $request->getParsedBody();
    
    $secretData = Auth::generate2FASecret($user);
    
    // Verify the code
    $totp = new \OTPHP\TOTP('GAINZ', $secretData['secret'], 30, 'sha1', 6);
    
    if ($totp->now() === $data['code']) {
        Auth::enable2FA($user->id, $secretData['secret']);
        FlashMessage::success('auth.2fa_enabled');
    } else {
        FlashMessage::error('auth.invalid_2fa_code');
    }
    
    return $response->withHeader('Location', '/GAINZ/public/profile/2fa')->withStatus(302);
})->setName('profile.2fa.enable');

$app->post('/profile/2fa/disable', function (Request $request, Response $response) {
    Auth::requireAuth('/login');
    $user = Auth::getCurrentUser();
    $data = $request->getParsedBody();
    
    // Verify password
    $bean = R::load('users', $user->id);
    if (password_verify($data['password'], $bean->password_hash)) {
        Auth::disable2FA($user->id);
        FlashMessage::success('auth.2fa_disabled');
    } else {
        FlashMessage::error('auth.invalid_password');
    }
    
    return $response->withHeader('Location', '/GAINZ/public/profile/2fa')->withStatus(302);
})->setName('profile.2fa.disable');

// ─── Admin Routes ───
$app->group('/admin', function ($group) {
    $group->get('', function (Request $request, Response $response) {
        Auth::requireAdmin('/');
        $twig = Twig::fromRequest($request);
        
        $userCount = R::count('users');
        $exerciseCount = R::count('exercise');
        $sessionCount = R::count('workout_session');
        
        return $twig->render($response, 'admin/dashboard.html.twig', [
            'stats' => [
                'users' => $userCount,
                'exercises' => $exerciseCount,
                'sessions' => $sessionCount
            ]
        ]);
    })->setName('admin.dashboard');
    
    // Admin: Exercises CRUD
    $group->get('/exercises', function (Request $request, Response $response) {
        Auth::requireAdmin('/');
        $twig = Twig::fromRequest($request);
        
        $exercises = R::findAll('exercise');
        $categories = R::findAll('category');
        
        return $twig->render($response, 'admin/exercises/index.html.twig', [
            'exercises' => array_map(fn($b) => \App\Models\Exercise::fromBean($b), $exercises),
            'categories' => array_map(fn($b) => \App\Models\Category::fromBean($b), $categories)
        ]);
    })->setName('admin.exercises');
    
    $group->post('/exercises', function (Request $request, Response $response) {
        Auth::requireAdmin('/');
        $data = $request->getParsedBody();
        
        $errors = \App\Utilities\Validator::validateExercise($data);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                FlashMessage::error($error);
            }
            return $response->withHeader('Location', '/GAINZ/public/admin/exercises')->withStatus(302);
        }
        
        $bean = R::dispense('exercise');
        $bean->category_id = $data['category_id'] ?: null;
        $bean->name_en = $data['name_en'];
        $bean->name_fr = $data['name_fr'];
        $bean->description_en = $data['description_en'] ?? null;
        $bean->description_fr = $data['description_fr'] ?? null;
        $bean->difficulty = $data['difficulty'] ?? 'beginner';
        $bean->equipment = $data['equipment'] ?? null;
        $bean->is_active = 1;
        
        R::store($bean);
        
        FlashMessage::success('admin.created');
        return $response->withHeader('Location', '/GAINZ/public/admin/exercises')->withStatus(302);
    })->setName('admin.exercises.store');
    
    $group->post('/exercises/{id}/delete', function (Request $request, Response $response, array $args) {
        Auth::requireAdmin('/');
        $id = (int) $args['id'];
        
        R::trash(R::load('exercise', $id));
        
        FlashMessage::success('admin.deleted');
        return $response->withHeader('Location', '/GAINZ/public/admin/exercises')->withStatus(302);
    })->setName('admin.exercises.delete');
    
    // Admin: Categories CRUD
    $group->get('/categories', function (Request $request, Response $response) {
        Auth::requireAdmin('/');
        $twig = Twig::fromRequest($request);
        
        $categories = R::findAll('category');
        
        return $twig->render($response, 'admin/categories/index.html.twig', [
            'categories' => array_map(fn($b) => \App\Models\Category::fromBean($b), $categories)
        ]);
    })->setName('admin.categories');
    
    $group->post('/categories', function (Request $request, Response $response) {
        Auth::requireAdmin('/');
        $data = $request->getParsedBody();
        
        $bean = R::dispense('category');
        $bean->name_en = $data['name_en'];
        $bean->name_fr = $data['name_fr'];
        $bean->description_en = $data['description_en'] ?? null;
        $bean->description_fr = $data['description_fr'] ?? null;
        
        R::store($bean);
        
        FlashMessage::success('admin.created');
        return $response->withHeader('Location', '/GAINZ/public/admin/categories')->withStatus(302);
    })->setName('admin.categories.store');
    
    $group->post('/categories/{id}/delete', function (Request $request, Response $response, array $args) {
        Auth::requireAdmin('/');
        $id = (int) $args['id'];
        
        R::trash(R::load('category', $id));
        
        FlashMessage::success('admin.deleted');
        return $response->withHeader('Location', '/GAINZ/public/admin/categories')->withStatus(302);
    })->setName('admin.categories.delete');
});

// ─── API Routes ───
$app->group('/api', function ($group) {
    // Public workout sessions count
    $group->get('/stats', function (Request $request, Response $response) {
        $stats = [
            'total_users' => R::count('users'),
            'total_exercises' => R::count('exercise', 'is_active = 1'),
            'total_programs' => R::count('training_programs', 'is_public = 1'),
            'total_sessions' => R::count('workout_session')
        ];
        
        $response->getBody()->write(json_encode($stats));
        return $response->withHeader('Content-Type', 'application/json');
    })->setName('api.stats');
    
    // Get exercises list
    $group->get('/exercises', function (Request $request, Response $response) {
        $i18n = I18n::getInstance();
        $locale = $i18n->getLocale();
        
        $exercises = R::findAll('exercise', 'is_active = 1');
        $data = [];
        
        foreach ($exercises as $bean) {
            $exercise = \App\Models\Exercise::fromBean($bean);
            $data[] = [
                'id' => $exercise->id,
                'name' => $exercise->getName($locale),
                'difficulty' => $exercise->difficulty,
                'category_id' => $exercise->categoryId
            ];
        }
        
        $response->getBody()->write(json_encode(['exercises' => $data]));
        return $response->withHeader('Content-Type', 'application/json');
    })->setName('api.exercises');
    
    // Get programs list
    $group->get('/programs', function (Request $request, Response $response) {
        $i18n = I18n::getInstance();
        $locale = $i18n->getLocale();
        
        $programs = R::findAll('training_programs', 'is_public = 1');
        $data = [];
        
        foreach ($programs as $bean) {
            $program = \App\Models\TrainingProgram::fromBean($bean);
            $data[] = [
                'id' => $program->id,
                'name' => $program->getName($locale),
                'description' => $program->getDescription($locale)
            ];
        }
        
        $response->getBody()->write(json_encode(['programs' => $data]));
        return $response->withHeader('Content-Type', 'application/json');
    })->setName('api.programs');
});

// Run the application
$app->run();