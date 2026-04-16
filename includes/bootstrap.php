<?php
declare(strict_types=1);

session_start();

const FW_ADMIN_SESSION_KEY = 'fw_admin_authenticated';
const FW_CONTENT_FILE = __DIR__ . '/../data/content.json';

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

function default_site_content(): array
{
    return [
        'companyInfo' => [
            'name' => 'Frowear Productions',
            'brandMark' => 'FP',
            'tagline' => 'Design-led engineering for digital systems',
            'heroEyebrow' => 'UI Projects. Systems. APIs. Integrations.',
            'heroTitle' => 'High-tech digital work built for launch, scale, and real customer flow.',
            'heroDescription' => 'Frowear Productions designs and engineers sharp websites, complete systems, polished interfaces, connected integrations, and custom special projects for teams that need the work to feel fast and look confident.',
            'primaryCtaLabel' => 'Request a Free Quote',
            'secondaryCtaLabel' => 'View Project Gallery',
            'companyHeading' => 'A visual engineering studio for modern digital work.',
            'companyIntro' => 'Company details, services, skills, brand direction, and open opportunities all run from one editable content model.',
            'companyNarrative' => 'The studio focuses on product-facing websites, operational systems, connected services, and custom interface builds that need both design confidence and engineering discipline.',
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
            ['title' => 'Special Projects', 'description' => 'Custom interactive builds where product design and engineering need to move together.'],
        ],
        'skills' => [
            ['name' => 'Frontend Architecture', 'summary' => 'Responsive interfaces, interaction systems, and UI patterns built to stay clear under real product growth.'],
            ['name' => 'System Design', 'summary' => 'Operational thinking across user roles, states, handoffs, and the internal logic behind working products.'],
            ['name' => 'API Integration', 'summary' => 'Reliable service connections for CRM, payments, reporting, automation, and external platform workflows.'],
            ['name' => 'Visual Design Direction', 'summary' => 'High-tech presentation, polished layout rhythm, and stronger hierarchy for modern digital products.'],
            ['name' => 'Admin Workflows', 'summary' => 'Content editing surfaces and internal controls for teams that need to update real business information.'],
            ['name' => 'Launch Delivery', 'summary' => 'Pragmatic scoping, implementation, and iteration from brief through working release.'],
        ],
        'opportunities' => [
            ['title' => 'New Website Build', 'summary' => 'A fresh digital presence for a launch, service business, or product-facing brand.', 'commitment' => 'Project-based', 'focus' => 'Strategy, UI, and implementation'],
            ['title' => 'System Modernization', 'summary' => 'Replace fragmented tools with a cleaner internal interface and connected workflow.', 'commitment' => 'Phased engagement', 'focus' => 'Dashboards, approvals, and reporting'],
            ['title' => 'Integration Sprint', 'summary' => 'Connect payments, CRM, scheduling, analytics, and customer follow-up systems.', 'commitment' => 'Short delivery sprint', 'focus' => 'Automation and operational visibility'],
            ['title' => 'Retained Product Support', 'summary' => 'Ongoing UI, feature, and system refinement for teams with continuing product work.', 'commitment' => 'Monthly retainer', 'focus' => 'Iteration and expansion'],
        ],
        'projects' => [
            [
                'title' => 'Launch Platform',
                'category' => 'websites',
                'status' => 'UI Refresh',
                'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Developer workstation showing a modern website interface and code editor',
                'summary' => 'A product-facing site with sharp storytelling, strong conversion paths, and a faster visual system across every key page.',
                'points' => ['Landing flow, feature sections, and quote capture', 'Responsive UI tuned for mobile and desktop clarity'],
            ],
            [
                'title' => 'Ops Command Center',
                'category' => 'systems',
                'status' => 'Operations',
                'image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Team collaborating in front of multiple dashboards and system screens',
                'summary' => 'A complete working system for teams managing delivery, approvals, client status, and service performance in one connected interface.',
                'points' => ['Role-based views and pipeline monitoring', 'Clear handoff logic for internal teams and clients'],
            ],
            [
                'title' => 'Service Layer',
                'category' => 'apis',
                'status' => 'Data Flow',
                'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Close-up technology hardware representing backend API and service infrastructure',
                'summary' => 'An API-first build that exposes clean endpoints, event logic, and reliable data movement between products, partners, and reporting tools.',
                'points' => ['Structured endpoints with predictable contracts', 'Auth, observability, and integration-ready payloads'],
            ],
            [
                'title' => 'Connected Revenue Flow',
                'category' => 'integrations',
                'status' => 'Automation',
                'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Analytics dashboard and charts used for business integration monitoring',
                'summary' => 'A customer and finance integration that joins purchase activity, fulfillment signals, analytics, and notifications into one reliable operating picture.',
                'points' => ['CRM, payments, and reporting connected end to end', 'Operational alerts surfaced inside a clean UI'],
            ],
            [
                'title' => 'Interactive Control Surface',
                'category' => 'special',
                'status' => 'Custom Build',
                'image' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'High-tech digital interface with illuminated controls for a custom project',
                'summary' => 'A custom interface for a high-visibility internal tool where interaction design, motion, and engineering quality all need to land together.',
                'points' => ['Dynamic views for live state and fast operator action', 'Custom components tuned to the workflow itself'],
            ],
            [
                'title' => 'Signature Experience Site',
                'category' => 'websites',
                'status' => 'Premium UI',
                'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                'alt' => 'Creative product team shaping a premium digital experience together',
                'summary' => 'A visually driven build for brands that need a stronger presence, better pacing, and a more memorable first impression without losing usability.',
                'points' => ['Immersive section transitions and clear content hierarchy', 'Design system patterns ready to scale past launch'],
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
    $directory = dirname(FW_CONTENT_FILE);
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

function admin_key(): string
{
    return env_value('FW_ADMIN_KEY', '') ?? '';
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
