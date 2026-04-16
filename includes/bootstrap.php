<?php
declare(strict_types=1);

session_start();

const FW_ADMIN_SESSION_KEY = 'fw_admin_authenticated';
const FW_CONTENT_FILE = __DIR__ . '/../data/content.json';
const FW_DATA_DIR = __DIR__ . '/../data';
const FW_SITE_CONTENT_KEY = 'primary';

function load_env_file(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
            continue;
        }

        [$name, $value] = array_map('trim', explode('=', $trimmed, 2));
        if ($name === '') {
            continue;
        }

        $value = trim($value, "\"'");
        if (getenv($name) === false) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
        }
    }
}

load_env_file(__DIR__ . '/../.env');

function env_value(string $name, ?string $default = null): ?string
{
    $value = $_ENV[$name] ?? getenv($name);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

function db_host(): string
{
    return env_value('DB_HOST', 'localhost') ?? 'localhost';
}

function db_name(): string
{
    return env_value('DB_NAME', '') ?? '';
}

function db_user(): string
{
    return env_value('DB_USER', '') ?? '';
}

function db_pass(): string
{
    return env_value('DB_PASS', '') ?? '';
}

function db_is_configured(): bool
{
    return db_name() !== '' && db_user() !== '';
}

function db_connection(): ?PDO
{
    static $pdo = false;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if ($pdo === null) {
        return null;
    }

    if (!db_is_configured()) {
        $pdo = null;
        return null;
    }

    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', db_host(), db_name()),
            db_user(),
            db_pass(),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        return $pdo;
    } catch (Throwable) {
        $pdo = null;
        return null;
    }
}

function ensure_site_content_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS site_content (
            content_key VARCHAR(120) NOT NULL PRIMARY KEY,
            content_json LONGTEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function default_site_content(): array
{
    return [
        'companyInfo' => [
            'name' => 'Frowear Productions',
            'brandMark' => 'FP',
            'tagline' => 'Design-led engineering for digital systems',
            'heroEyebrow' => 'UI Projects. Systems. APIs. Integrations.',
            'heroTitle' => 'High-tech digital work built for launch, scale, opportunity flow, and real collaboration.',
            'heroDescription' => 'Frowear Productions designs websites, systems, APIs, integrations, and network-ready project platforms that can support companies, talent, and active opportunities in one product surface.',
            'primaryCtaLabel' => 'Request a Free Quote',
            'secondaryCtaLabel' => 'View Project Gallery',
            'companyHeading' => 'A visual engineering studio for modern digital work.',
            'companyIntro' => 'Company details, services, skills, projects, company profiles, talent profiles, and open opportunities all run from one editable content model.',
            'companyNarrative' => 'The studio focuses on product-facing websites, operational systems, connected services, and custom interface builds while shaping the platform toward multi-company collaboration and talent participation.',
            'studioLabel' => 'Visual Coding Engineering',
            'responseWindow' => 'Free quote replies within two business days',
            'location' => 'Remote delivery with collaborative planning',
            'email' => 'hello@frowear.com',
        ],
        'branding' => [
            'positioning' => 'Premium UI direction with build-ready engineering execution.',
            'voice' => 'Clear, technical, modern, and high-trust. Strong enough for product work, sharp enough for custom client systems.',
            'focusAreas' => ['Websites', 'Systems', 'APIs', 'Integrations'],
            'highlights' => [
                'Dark blue neon visual system',
                'Design-first engineering language',
                'Admin-editable content sections',
                'Project gallery with filtered categories',
            ],
        ],
        'theme' => [
            'bg' => '#040914',
            'bgDeep' => '#081224',
            'panel' => 'rgba(10, 20, 37, 0.9)',
            'panelStrong' => 'rgba(8, 16, 31, 0.96)',
            'line' => 'rgba(104, 199, 255, 0.2)',
            'lineStrong' => 'rgba(104, 199, 255, 0.44)',
            'text' => '#f5fbff',
            'muted' => '#95a9c0',
            'cyan' => '#4be7ff',
            'green' => '#9cff8f',
            'pink' => '#ff6ad5',
        ],
        'services' => [
            ['title' => 'Websites', 'description' => 'Marketing sites, launch pages, service experiences, portals, and premium UI refreshes.'],
            ['title' => 'Complete Systems', 'description' => 'Dashboards, client systems, internal tools, approvals, workflows, and reporting interfaces.'],
            ['title' => 'APIs', 'description' => 'Structured service layers, backend contracts, event logic, integrations, and connected data.'],
            ['title' => 'Integrations', 'description' => 'Payments, CRM, scheduling, notifications, analytics, and partner platform connections.'],
            ['title' => 'Design Systems', 'description' => 'Reusable interface language, visual rules, spacing systems, and high-clarity components.'],
            ['title' => 'Talent Platforms', 'description' => 'Profiles, opportunities, project applications, company matching, and collaboration-ready records.'],
        ],
        'skills' => [
            ['name' => 'Frontend Architecture', 'summary' => 'Responsive interfaces, interaction systems, and UI patterns built to stay clear under real product growth.'],
            ['name' => 'System Design', 'summary' => 'Operational thinking across user roles, states, handoffs, and the internal logic behind working products.'],
            ['name' => 'API Integration', 'summary' => 'Reliable service connections for CRM, payments, reporting, automation, and external platform workflows.'],
            ['name' => 'Visual Design Direction', 'summary' => 'High-tech presentation, polished layout rhythm, and stronger hierarchy for modern digital products.'],
            ['name' => 'Admin Workflows', 'summary' => 'Content editing surfaces and internal controls for teams that need to update real business information.'],
            ['name' => 'Marketplace Planning', 'summary' => 'Data structures for companies, talent, opportunities, applications, and project participation.'],
        ],
        'opportunities' => [
            ['title' => 'New Website Build', 'summary' => 'A fresh digital presence for a launch, service business, or product-facing brand.', 'commitment' => 'Project-based', 'focus' => 'Strategy, UI, and implementation', 'company' => 'Northline Studio', 'skills' => ['UI Design', 'Frontend'], 'applyLabel' => 'Apply to Build Team'],
            ['title' => 'System Modernization', 'summary' => 'Replace fragmented tools with a cleaner internal interface and connected workflow.', 'commitment' => 'Phased engagement', 'focus' => 'Dashboards, approvals, and reporting', 'company' => 'Harbor Ops', 'skills' => ['Product Systems', 'PHP'], 'applyLabel' => 'Apply for System Role'],
            ['title' => 'Integration Sprint', 'summary' => 'Connect payments, CRM, scheduling, analytics, and customer follow-up systems.', 'commitment' => 'Short delivery sprint', 'focus' => 'Automation and operational visibility', 'company' => 'Atlas Commerce', 'skills' => ['APIs', 'Integrations'], 'applyLabel' => 'Apply for Sprint Team'],
            ['title' => 'Retained Product Support', 'summary' => 'Ongoing UI, feature, and system refinement for teams with continuing product work.', 'commitment' => 'Monthly retainer', 'focus' => 'Iteration and expansion', 'company' => 'Frowear Productions', 'skills' => ['Frontend', 'Product Design'], 'applyLabel' => 'Join Retained Team'],
        ],
        'projects' => [
            [
                'title' => 'Launch Platform',
                'category' => 'websites',
                'status' => 'UI Refresh',
                'company' => 'Frowear Productions',
                'stage' => 'Build phase',
                'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Developer workstation showing a modern website interface and code editor',
                'summary' => 'A product-facing site with sharp storytelling, strong conversion paths, and a faster visual system across every key page.',
                'points' => ['Landing flow, feature sections, and quote capture', 'Responsive UI tuned for mobile and desktop clarity'],
                'stack' => ['PHP', 'JavaScript', 'UI System'],
                'needs' => ['Conversion design', 'Frontend polish'],
            ],
            [
                'title' => 'Ops Command Center',
                'category' => 'systems',
                'status' => 'Operations',
                'company' => 'Harbor Ops',
                'stage' => 'Workflow mapping',
                'image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Team collaborating in front of multiple dashboards and system screens',
                'summary' => 'A complete working system for teams managing delivery, approvals, client status, and service performance in one connected interface.',
                'points' => ['Role-based views and pipeline monitoring', 'Clear handoff logic for internal teams and clients'],
                'stack' => ['PHP', 'MySQL', 'Admin Panels'],
                'needs' => ['Process design', 'Reporting UX'],
            ],
            [
                'title' => 'Service Layer',
                'category' => 'apis',
                'status' => 'Data Flow',
                'company' => 'Atlas Commerce',
                'stage' => 'API integration',
                'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Close-up technology hardware representing backend API and service infrastructure',
                'summary' => 'An API-first build that exposes clean endpoints, event logic, and reliable data movement between products, partners, and reporting tools.',
                'points' => ['Structured endpoints with predictable contracts', 'Auth, observability, and integration-ready payloads'],
                'stack' => ['REST APIs', 'Webhook Flows', 'Auth'],
                'needs' => ['Backend integration', 'Monitoring'],
            ],
            [
                'title' => 'Connected Revenue Flow',
                'category' => 'integrations',
                'status' => 'Automation',
                'company' => 'Northline Studio',
                'stage' => 'Cross-platform sync',
                'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Analytics dashboard and charts used for business integration monitoring',
                'summary' => 'A customer and finance integration that joins purchase activity, fulfillment signals, analytics, and notifications into one reliable operating picture.',
                'points' => ['CRM, payments, and reporting connected end to end', 'Operational alerts surfaced inside a clean UI'],
                'stack' => ['CRM', 'Payments', 'Analytics'],
                'needs' => ['Automation logic', 'Data hygiene'],
            ],
            [
                'title' => 'Interactive Control Surface',
                'category' => 'special',
                'status' => 'Custom Build',
                'company' => 'Frowear Productions',
                'stage' => 'Prototype',
                'image' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'High-tech digital interface with illuminated controls for a custom project',
                'summary' => 'A custom interface for a high-visibility internal tool where interaction design, motion, and engineering quality all need to land together.',
                'points' => ['Dynamic views for live state and fast operator action', 'Custom components tuned to the workflow itself'],
                'stack' => ['Custom UI', 'Motion', 'Ops Controls'],
                'needs' => ['Interface design', 'Interaction polish'],
            ],
            [
                'title' => 'Signature Experience Site',
                'category' => 'websites',
                'status' => 'Premium UI',
                'company' => 'Northline Studio',
                'stage' => 'Launch prep',
                'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Creative product team shaping a premium digital experience together',
                'summary' => 'A visually driven build for brands that need a stronger presence, better pacing, and a more memorable first impression without losing usability.',
                'points' => ['Immersive section transitions and clear content hierarchy', 'Design system patterns ready to scale past launch'],
                'stack' => ['Brand UI', 'Responsive Design', 'Content Systems'],
                'needs' => ['Brand storytelling', 'Launch QA'],
            ],
        ],
        'companies' => [
            [
                'name' => 'Northline Studio',
                'industry' => 'Brand and digital product',
                'location' => 'New York, NY',
                'bio' => 'Northline Studio uses the platform to post premium site builds, brand systems, and launch-oriented opportunities.',
                'skills' => ['Brand Systems', 'Creative Direction', 'Frontend'],
                'opportunities' => ['Signature site refresh', 'Launch microsite team'],
            ],
            [
                'name' => 'Harbor Ops',
                'industry' => 'Operations software',
                'location' => 'Atlanta, GA',
                'bio' => 'Harbor Ops lists workflow modernization work and system design needs for internal product and client operations teams.',
                'skills' => ['Operations UX', 'Admin Systems', 'Reporting'],
                'opportunities' => ['Dashboard redesign', 'Ops command center'],
            ],
            [
                'name' => 'Atlas Commerce',
                'industry' => 'Commerce and data',
                'location' => 'Austin, TX',
                'bio' => 'Atlas Commerce uses integrations, service layers, and data visibility to scale customer and finance workflows.',
                'skills' => ['APIs', 'Integrations', 'Analytics'],
                'opportunities' => ['Integration sprint', 'Service layer expansion'],
            ],
        ],
        'talent' => [
            [
                'name' => 'Ariel Coleman',
                'role' => 'Product Designer',
                'bio' => 'Designs clear product surfaces, stronger hierarchy, and better onboarding across dashboards and launch sites.',
                'skills' => ['UI Design', 'Design Systems', 'Journey Mapping'],
                'availability' => 'Open for project work',
                'interests' => ['Web platforms', 'Client portals'],
            ],
            [
                'name' => 'Marcus Lin',
                'role' => 'Frontend Engineer',
                'bio' => 'Builds interface systems with responsive structure, component discipline, and tight product detail.',
                'skills' => ['JavaScript', 'PHP', 'UI Architecture'],
                'availability' => 'Available part time',
                'interests' => ['Admin systems', 'Project marketplaces'],
            ],
            [
                'name' => 'Sanaa Brooks',
                'role' => 'Integration Engineer',
                'bio' => 'Connects APIs, automation layers, and operational data so internal tools and customer flows stay aligned.',
                'skills' => ['APIs', 'Webhooks', 'Data Sync'],
                'availability' => 'Available for sprint work',
                'interests' => ['Integrations', 'Automation projects'],
            ],
        ],
    ];
}

function merge_deep(array $base, array $incoming): array
{
    foreach ($incoming as $key => $value) {
        if (isset($base[$key]) && is_array($base[$key]) && is_array($value) && array_is_list($base[$key]) === false && array_is_list($value) === false) {
            $base[$key] = merge_deep($base[$key], $value);
            continue;
        }

        $base[$key] = $value;
    }

    return $base;
}

function get_site_content(): array
{
    $defaults = default_site_content();
    $pdo = db_connection();

    if ($pdo instanceof PDO) {
        try {
            ensure_site_content_table($pdo);
            $statement = $pdo->prepare('SELECT content_json FROM site_content WHERE content_key = :content_key LIMIT 1');
            $statement->execute(['content_key' => FW_SITE_CONTENT_KEY]);
            $row = $statement->fetch();

            if (is_array($row) && isset($row['content_json'])) {
                $decoded = json_decode((string) $row['content_json'], true);
                if (is_array($decoded)) {
                    return merge_deep($defaults, $decoded);
                }
            }
        } catch (Throwable) {
            // Fall back to file storage below.
        }
    }

    if (!is_file(FW_CONTENT_FILE) || !is_readable(FW_CONTENT_FILE)) {
        return $defaults;
    }

    $raw = file_get_contents(FW_CONTENT_FILE);
    if ($raw === false) {
        return $defaults;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $defaults;
    }

    return merge_deep($defaults, $decoded);
}

function save_site_content(array $content): void
{
    $pdo = db_connection();
    if ($pdo instanceof PDO) {
        ensure_site_content_table($pdo);
        $json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException('Unable to encode site content JSON.');
        }

        $statement = $pdo->prepare(
            'INSERT INTO site_content (content_key, content_json)
             VALUES (:content_key, :content_json)
             ON DUPLICATE KEY UPDATE content_json = VALUES(content_json)'
        );
        $statement->execute([
            'content_key' => FW_SITE_CONTENT_KEY,
            'content_json' => $json,
        ]);
        return;
    }

    $directory = data_directory();
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create data directory.');
    }

    $json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('Unable to encode content JSON.');
    }

    if (file_put_contents(FW_CONTENT_FILE, $json . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException('Unable to write content file.');
    }
}

function data_directory(): string
{
    return FW_DATA_DIR;
}

function admin_key(): string
{
    return env_value('FW_ADMIN_KEY', '') ?? '';
}

function webhook_secret(): string
{
    return env_value('FW_WEBHOOK_SECRET', '') ?? '';
}

function is_admin_authenticated(): bool
{
    return ($_SESSION[FW_ADMIN_SESSION_KEY] ?? false) === true;
}

function require_admin(): void
{
    if (!is_admin_authenticated()) {
        json_response(['ok' => false, 'message' => 'Unauthorized.'], 401);
    }
}

function request_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
