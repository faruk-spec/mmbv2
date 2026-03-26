<?php
/**
 * ResumeX AI Controller
 * Provides AI-powered (Hugging Face) and rule-based resume suggestions.
 *
 * @package MMB\Projects\ResumeX\Controllers
 */

namespace Projects\ResumeX\Controllers;

use Core\Security;
use Core\Logger;

class AIController
{
    /** Hugging Face Inference API endpoint */
    private const HF_API_URL = 'https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.1';

    /** Request timeout in seconds */
    private const HF_TIMEOUT = 20;

    public function __construct()
    {
        header('Content-Type: application/json');
    }

    /* ── suggest-all (AI-powered combined endpoint) ───────────── */

    /**
     * Returns summary, skills, and bullets together using the Hugging Face API,
     * falling back to rule-based suggestions if the API is unavailable.
     */
    public function suggestAll(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle   = trim($_POST['job_title']        ?? '');
        $experience = trim($_POST['experience']        ?? '');
        $skills     = trim($_POST['skills']            ?? '');
        $company    = trim($_POST['company']           ?? '');
        $expYears   = (int) ($_POST['experience_years'] ?? 0);

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        // Attempt Hugging Face AI generation
        $aiResult = $this->callHuggingFaceAPI($jobTitle, $experience ?: ($expYears > 0 ? "{$expYears} years" : 'entry level'));

        if ($aiResult !== null) {
            echo json_encode([
                'success'    => true,
                'ai_powered' => true,
                'summary'    => $aiResult['summary']  ?? '',
                'skills'     => array_slice($aiResult['skills'] ?? [], 0, 8),
                'bullets'    => $aiResult['bullets']  ?? [],
                'suggestions'=> [$aiResult['summary'] ?? ''],
            ]);
            exit;
        }

        // Fallback: rule-based
        echo json_encode([
            'success'    => true,
            'ai_powered' => false,
            'summary'    => $this->buildSummaries($jobTitle, $expYears, $skills)[0] ?? '',
            'skills'     => array_slice($this->getSkillsForRole($jobTitle), 0, 8),
            'bullets'    => $this->buildBullets($jobTitle, $company),
            'suggestions'=> $this->buildSummaries($jobTitle, $expYears, $skills),
        ]);
        exit;
    }

    /* ── suggest-summary ──────────────────────────────────────── */
    public function suggestSummary(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle   = trim($_POST['job_title']        ?? '');
        $experience = trim($_POST['experience']        ?? '');
        $expYears   = (int) ($_POST['experience_years'] ?? 0);
        $skills     = trim($_POST['skills']            ?? '');

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        // Try Hugging Face API first
        $expLabel = $experience ?: ($expYears > 0 ? "{$expYears} years" : 'entry level');
        $aiResult = $this->callHuggingFaceAPI($jobTitle, $expLabel);
        if ($aiResult !== null && !empty($aiResult['summary'])) {
            echo json_encode([
                'success'    => true,
                'ai_powered' => true,
                'suggestions'=> [$aiResult['summary']],
            ]);
            exit;
        }

        // Fallback: rule-based
        echo json_encode([
            'success'    => true,
            'ai_powered' => false,
            'suggestions'=> $this->buildSummaries($jobTitle, $expYears, $skills),
        ]);
        exit;
    }

    /* ── suggest-skills ───────────────────────────────────────── */
    public function suggestSkills(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle   = trim($_POST['job_title'] ?? '');
        $experience = trim($_POST['experience'] ?? '');

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        // Try Hugging Face API first
        $aiResult = $this->callHuggingFaceAPI($jobTitle, $experience ?: 'mid level');
        if ($aiResult !== null && !empty($aiResult['skills'])) {
            echo json_encode([
                'success'    => true,
                'ai_powered' => true,
                'skills'     => array_slice($aiResult['skills'], 0, 8),
            ]);
            exit;
        }

        // Fallback: rule-based
        echo json_encode([
            'success'    => true,
            'ai_powered' => false,
            'skills'     => $this->getSkillsForRole($jobTitle),
        ]);
        exit;
    }

    /* ── suggest-bullets ──────────────────────────────────────── */
    public function suggestBullets(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle   = trim($_POST['job_title'] ?? '');
        $company    = trim($_POST['company']   ?? '');
        $experience = trim($_POST['experience'] ?? '');

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        // Try Hugging Face API first
        $aiResult = $this->callHuggingFaceAPI($jobTitle, $experience ?: 'mid level');
        if ($aiResult !== null && !empty($aiResult['bullets'])) {
            echo json_encode([
                'success'    => true,
                'ai_powered' => true,
                'bullets'    => $aiResult['bullets'],
            ]);
            exit;
        }

        // Fallback: rule-based
        echo json_encode([
            'success'    => true,
            'ai_powered' => false,
            'bullets'    => $this->buildBullets($jobTitle, $company),
        ]);
        exit;
    }

    /* ── score ────────────────────────────────────────────────── */
    public function score(): void
    {
        Security::validateCsrfToken($this->getToken());

        $body       = file_get_contents('php://input');
        $payload    = json_decode($body, true) ?? [];
        $rd         = $payload['resume_data'] ?? [];

        $score       = 0;
        $suggestions = [];
        $breakdown   = [];

        // ── Contact (20 pts) ────────────────────────────────────
        $contact      = $rd['contact'] ?? [];
        $contactScore = 0;
        foreach (['name' => 5, 'email' => 5, 'phone' => 4, 'location' => 3] as $f => $pts) {
            if (!empty($contact[$f])) {
                $contactScore += $pts;
            } else {
                $suggestions[] = "Add your {$f} to contact information.";
            }
        }
        // Bonus: LinkedIn (+2), portfolio (+1)
        if (!empty($contact['linkedin'])) { $contactScore = min(20, $contactScore + 2); }
        else { $suggestions[] = 'Add your LinkedIn profile URL to increase visibility.'; }
        if (!empty($contact['website'])) { $contactScore = min(20, $contactScore + 1); }
        $breakdown['contact'] = ['score' => $contactScore, 'max' => 20, 'label' => 'Contact Info'];
        $score += $contactScore;

        // ── Summary (20 pts) ────────────────────────────────────
        $summary    = $rd['summary'] ?? '';
        $wordCount  = str_word_count($summary);
        if ($wordCount >= 50) {
            $summaryScore = 20;
        } elseif ($wordCount >= 25) {
            $summaryScore = 13;
            $suggestions[] = 'Expand your professional summary (aim for 50+ words).';
        } elseif ($wordCount >= 10) {
            $summaryScore = 7;
            $suggestions[] = 'Your summary is too short. Write 50+ words to make it impactful.';
        } else {
            $summaryScore = 0;
            $suggestions[] = 'Add a professional summary — it\'s one of the first things recruiters read.';
        }
        $breakdown['summary'] = ['score' => $summaryScore, 'max' => 20, 'label' => 'Professional Summary'];
        $score += $summaryScore;

        // ── Experience (25 pts) ─────────────────────────────────
        $experience      = $rd['experience'] ?? [];
        $expCount        = count($experience);
        $bulletsPerEntry = 0;
        foreach ($experience as $exp) {
            $bulletsPerEntry += count($exp['bullets'] ?? []);
        }
        $avgBullets = $expCount > 0 ? $bulletsPerEntry / $expCount : 0;

        if ($expCount >= 3)      { $expScore = 20; }
        elseif ($expCount === 2) { $expScore = 15; $suggestions[] = 'Add more work experience entries to strengthen your resume.'; }
        elseif ($expCount === 1) { $expScore = 8;  $suggestions[] = 'Add more work experience entries.'; }
        else                    { $expScore = 0;  $suggestions[] = 'Add at least one work experience entry.'; }

        // Bullets bonus (up to 5 pts)
        if ($avgBullets >= 3)       { $expScore = min(25, $expScore + 5); }
        elseif ($avgBullets >= 1)   { $expScore = min(25, $expScore + 2); $suggestions[] = 'Add bullet points to each job — aim for 3+ per position.'; }
        else if ($expCount > 0)     { $suggestions[] = 'Add achievement bullet points to your work experience entries.'; }

        $breakdown['experience'] = ['score' => $expScore, 'max' => 25, 'label' => 'Work Experience'];
        $score += $expScore;

        // ── Skills (15 pts) ─────────────────────────────────────
        $skills      = $rd['skills'] ?? [];
        $skillCount  = count($skills);
        if ($skillCount >= 10)     { $skillsScore = 15; }
        elseif ($skillCount >= 6)  { $skillsScore = 10; $suggestions[] = 'Add more skills (aim for 10+).'; }
        elseif ($skillCount >= 3)  { $skillsScore = 5;  $suggestions[] = 'Add more relevant skills to your resume.'; }
        else                       { $skillsScore = 0;  $suggestions[] = 'Add your technical and soft skills.'; }
        $breakdown['skills'] = ['score' => $skillsScore, 'max' => 15, 'label' => 'Skills'];
        $score += $skillsScore;

        // ── Education (10 pts) ──────────────────────────────────
        $education      = $rd['education'] ?? [];
        $educationScore = 0;
        if (!empty($education)) {
            $educationScore = 8;
            $edu0 = $education[0] ?? [];
            if (!empty($edu0['gpa']) || !empty($edu0['description'])) { $educationScore = 10; }
        } else {
            $suggestions[] = 'Add your education background.';
        }
        $breakdown['education'] = ['score' => $educationScore, 'max' => 10, 'label' => 'Education'];
        $score += $educationScore;

        // ── Extra sections (10 pts, 2.5 each) ──────────────────
        $extraScore = 0;
        foreach (['projects' => 'Projects', 'certifications' => 'Certifications', 'awards' => 'Awards & Achievements', 'publications' => 'Publications'] as $sec => $label) {
            if (!empty($rd[$sec])) { $extraScore += 2.5; }
        }
        $extraScore = (int) $extraScore;
        if ($extraScore < 5) {
            $suggestions[] = 'Add projects or certifications to differentiate yourself.';
        }
        $breakdown['extras'] = ['score' => $extraScore, 'max' => 10, 'label' => 'Extra Sections'];
        $score += $extraScore;

        $score = min(100, (int) $score);
        $grade = $score >= 90 ? 'A+' : ($score >= 80 ? 'A' : ($score >= 70 ? 'B' : ($score >= 55 ? 'C' : ($score >= 40 ? 'D' : 'F'))));
        $label = $score >= 90 ? 'Outstanding' : ($score >= 80 ? 'Strong' : ($score >= 70 ? 'Good' : ($score >= 55 ? 'Average' : ($score >= 40 ? 'Weak' : 'Incomplete'))));

        echo json_encode([
            'success'     => true,
            'score'       => $score,
            'grade'       => $grade,
            'label'       => $label,
            'breakdown'   => $breakdown,
            'suggestions' => $suggestions,
        ]);
        exit;
    }

    // ── Private helpers ──────────────────────────────────────────

    /**
     * Call Hugging Face Inference API (Mistral-7B-Instruct-v0.1) to generate
     * a professional summary, 8 relevant skills, and achievement bullet points.
     *
     * Returns an array with keys 'summary', 'skills', and 'bullets' on success,
     * or null when the API is unavailable / the token is not configured.
     *
     * On failure the real error is logged and the admin is notified; the caller
     * then falls back to the rule-based suggestion engine.
     *
     * @param string $jobTitle   e.g. "Software Engineer"
     * @param string $experience e.g. "5 years" | "entry level" | "senior"
     * @return array{summary:string,skills:string[],bullets:string[]}|null
     */
    private function callHuggingFaceAPI(string $jobTitle, string $experience): ?array
    {
        $token = defined('HUGGING_FACE_API_TOKEN') ? HUGGING_FACE_API_TOKEN : '';
        if (empty($token)) {
            return null; // Not configured – use rule-based silently
        }

        // Sanitize inputs: strip control characters and cap length to prevent prompt injection
        $safeJobTitle   = mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', '', $jobTitle),   0, 100);
        $safeExperience = mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', '', $experience), 0, 50);

        $prompt = "<s>[INST] You are a professional resume writer.\n"
            . "Given a job title and experience level, generate:\n"
            . "1. A professional summary (3-4 lines)\n"
            . "2. A list of exactly 8 relevant skills\n"
            . "3. Exactly 5 strong achievement-oriented bullet points for a resume\n\n"
            . "Job Title: {$safeJobTitle}\n"
            . "Experience Level: {$safeExperience}\n\n"
            . "Respond ONLY with a valid JSON object in this exact format (no markdown, no extra text):\n"
            . '{"summary":"...","skills":["...","...","...","...","...","...","...","..."],"bullets":["...","...","...","...","..."]}' . " [/INST]";

        $payload = json_encode([
            'inputs'     => $prompt,
            'parameters' => [
                'max_new_tokens'  => 512,
                'temperature'     => 0.7,
                'return_full_text'=> false,
            ],
        ]);

        $ch = curl_init(self::HF_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => self::HF_TIMEOUT,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($raw === false || !empty($curlErr)) {
            $this->notifyAdmin(
                "Hugging Face API cURL error: {$curlErr}",
                ['job_title' => $jobTitle, 'experience' => $experience]
            );
            return null;
        }

        if ($httpCode !== 200) {
            $this->notifyAdmin(
                "Hugging Face API returned HTTP {$httpCode}: {$raw}",
                ['job_title' => $jobTitle, 'experience' => $experience]
            );
            return null;
        }

        // The HF inference API wraps the result in an array
        $decoded = json_decode($raw, true);
        $text    = '';
        if (isset($decoded[0]['generated_text'])) {
            $text = $decoded[0]['generated_text'];
        } elseif (is_string($decoded)) {
            $text = $decoded;
        }

        return $this->parseAIResponse($text, $jobTitle, $experience);
    }

    /**
     * Extract the JSON payload from the model's generated text and validate
     * that it contains the required keys.  Returns null if parsing fails.
     */
    private function parseAIResponse(string $text, string $jobTitle, string $experience): ?array
    {
        // Find JSON object in the response (model may add surrounding text)
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $data = json_decode($m[0], true);
            if (
                is_array($data)
                && isset($data['summary'], $data['skills'], $data['bullets'])
                && is_string($data['summary'])
                && is_array($data['skills'])
                && is_array($data['bullets'])
                && count($data['skills']) >= 4
            ) {
                return [
                    'summary' => trim($data['summary']),
                    'skills'  => array_values(array_filter(array_map('trim', $data['skills']))),
                    'bullets' => array_values(array_filter(array_map('trim', $data['bullets']))),
                ];
            }
        }

        $this->notifyAdmin(
            'Hugging Face API response could not be parsed as valid JSON',
            ['job_title' => $jobTitle, 'experience' => $experience, 'raw_snippet' => mb_substr($text, 0, 300)]
        );
        return null;
    }

    /**
     * Log the real error for the admin and send an email notification.
     * Users see only a generic friendly message; admins receive full details.
     */
    private function notifyAdmin(string $realError, array $context = []): void
    {
        // Always write to application log
        Logger::error('[ResumeX AI] ' . $realError, $context);

        // Attempt to email the admin using the platform mail stack
        try {
            $adminEmail = $this->getAdminEmail();
            if (!empty($adminEmail) && class_exists('\\Core\\Email')) {
                $subject = '[' . (defined('APP_NAME') ? APP_NAME : 'ResumeX') . '] AI Suggestion Service Error';
                $body    = '<p><strong>AI service error in ResumeX resume maker:</strong></p>'
                    . '<p>' . htmlspecialchars($realError, ENT_QUOTES, 'UTF-8') . '</p>'
                    . '<pre>' . htmlspecialchars(json_encode($context, JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') . '</pre>'
                    . '<p><small>Time: ' . date('Y-m-d H:i:s T') . '</small></p>';

                \Core\Email::send($adminEmail, $subject, $body);
            }
        } catch (\Throwable $e) {
            // Never let notification failure surface to the end user
            Logger::error('[ResumeX AI] Admin notification email failed: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve the admin e-mail address from the settings table (if available).
     */
    private function getAdminEmail(): string
    {
        try {
            if (class_exists('\\Core\\Database')) {
                $db  = \Core\Database::getInstance();
                $row = $db->fetch(
                    "SELECT email FROM users WHERE role = 'admin' AND status = 'active' ORDER BY id ASC LIMIT 1"
                );
                return $row['email'] ?? '';
            }
        } catch (\Throwable $e) {
            // Silently ignore DB errors
        }
        return '';
    }

    private function getToken(): string
    {
        $body = file_get_contents('php://input');
        if (!empty($body)) {
            $p = json_decode($body, true);
            return $p['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        }
        return $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    }

    private function buildSummaries(string $jobTitle, int $years, string $skills): array
    {
        $jt  = ucwords($jobTitle);
        $jl  = strtolower($jobTitle);
        $yrs = $years > 0 ? "{$years}+" : 'several';
        $sk  = !empty($skills) ? ", specialising in {$skills}," : '';
        $skb = !empty($skills) ? " with core expertise in {$skills}" : '';
        $yr  = $years > 0 ? "Over {$years} years" : 'Extensive experience';

        // Role-specific opening sentences for more natural variation
        $techRoles = ['developer','engineer','programmer','backend','frontend','fullstack','devops','data'];
        $isTech = false;
        foreach ($techRoles as $kw) { if (str_contains($jl, $kw)) { $isTech = true; break; } }

        if ($isTech) {
            return [
                "{$yr} as a {$jt}{$sk} building scalable, production-grade systems. Driven by a passion for clean code, engineering excellence, and measurable business impact. Proven ability to own features end-to-end, collaborate cross-functionally, and mentor junior engineers in fast-paced agile environments.",
                "Impact-focused {$jt}{$skb}. {$yr} designing and shipping robust software solutions that serve real users at scale. Comfortable operating across the full stack — from architecture decisions to code reviews — with a track record of reducing tech debt and accelerating delivery cycles.",
                "Versatile {$jt} with {$yrs} years of experience{$sk} who thrives on solving ambiguous, high-impact problems. Passionate about developer experience, system reliability, and data-driven engineering decisions. Equally comfortable diving deep into technical detail and presenting at the executive level.",
                "{$yr} as a {$jt}{$skb}, turning complex product requirements into reliable, performant software. Known for taking ownership, fostering engineering best practices, and building collaborative team cultures that consistently ship quality work on time.",
                "Creative and analytical {$jt} with {$yrs} years of experience{$sk}. Combines strong technical depth with excellent communication skills to bridge the gap between engineering teams and business stakeholders. Committed to continuous learning and raising the bar in everything I ship.",
            ];
        }

        return [
            "Results-driven {$jt} with {$yrs} years of experience{$sk} delivering high-impact outcomes across diverse projects and teams. Skilled at translating complex challenges into practical solutions, with a consistent focus on quality, strategic alignment, and stakeholder value.",
            "Passionate and adaptable {$jt}{$skb}, bringing {$yrs} years of progressive experience. Recognised for building strong cross-functional relationships, driving data-informed decisions, and consistently exceeding expectations in fast-moving environments.",
            "Dynamic {$jt} with {$yrs} years of experience{$sk}. Adept at leading initiatives from concept to delivery, with a proven ability to manage competing priorities, mentor colleagues, and communicate clearly at all levels of an organisation.",
            "{$yr} as a {$jt}{$skb}. Known for a thoughtful, detail-oriented approach that ensures high standards without sacrificing pace. Committed to continuous improvement, team collaboration, and creating lasting positive impact in every role.",
            "Experienced {$jt} combining {$yrs} years of hands-on expertise{$sk} with strong analytical and interpersonal skills. Comfortable navigating ambiguity, aligning teams around shared goals, and delivering tangible results that move the needle on key business metrics.",
        ];
    }

    private function buildBullets(string $jobTitle, string $company): array
    {
        $jt = strtolower($jobTitle);
        $co = $company ? " at {$company}" : '';

        // Role-specific high-impact bullets
        if ($this->matchRole($jt, ['software engineer','developer','programmer','backend','frontend','fullstack','full stack','full-stack','web developer'])) {
            return [
                "Architected and delivered key product features{$co}, cutting time-to-release by 35% through improved CI/CD pipelines.",
                "Refactored legacy services, reducing technical debt by 40% and increasing code coverage from 48% to 87%.",
                "Mentored 3 junior engineers, conducting weekly code reviews and establishing team coding standards.",
                "Optimised critical database queries, achieving a 60% reduction in average response time under peak load.",
                "Led migration from monolith to microservices architecture, improving deployment frequency from monthly to daily.",
                "Collaborated with product and design to define requirements, consistently delivering sprint goals on schedule.",
                "Integrated third-party APIs and payment gateways, expanding platform revenue streams by 20%.",
            ];
        }

        if ($this->matchRole($jt, ['devops','sre','cloud','infrastructure','platform engineer'])) {
            return [
                "Designed and maintained Kubernetes clusters supporting 500k+ daily requests with 99.98% uptime.",
                "Automated infrastructure provisioning with Terraform, reducing manual setup time by 80%.",
                "Built end-to-end CI/CD pipelines cutting deployment time from 2 hours to 12 minutes.",
                "Implemented centralised logging and alerting (Prometheus/Grafana), reducing MTTR by 55%.",
                "Led cloud cost optimisation initiative saving \$120k annually through right-sizing and reserved instances.",
                "Developed disaster recovery runbooks, achieving RPO of 15 minutes and RTO of 30 minutes.",
                "Hardened security posture by implementing network policies, secrets management, and vulnerability scanning.",
            ];
        }

        if ($this->matchRole($jt, ['data scientist','machine learning','ml engineer','ai engineer','data analyst','data engineer'])) {
            return [
                "Developed predictive models achieving 94% accuracy, enabling data-driven product recommendations.",
                "Built automated ETL pipelines processing 5M+ daily records, improving data freshness by 70%.",
                "Designed A/B testing framework used across 12 product teams to validate feature releases.",
                "Reduced model training time by 65% through feature engineering and distributed computing.",
                "Created executive dashboards in Tableau/Power BI tracking 30+ KPIs across business units.",
                "Collaborated with engineering to deploy models to production, serving 50k+ predictions per day.",
                "Published internal research findings, leading to two patent applications.",
            ];
        }

        if ($this->matchRole($jt, ['designer','ux','ui','product designer','visual designer','graphic designer'])) {
            return [
                "Redesigned core user onboarding flow, improving 30-day retention by 28% and reducing drop-off by 40%.",
                "Conducted 40+ user research sessions and usability tests to validate design decisions.",
                "Built and maintained a cross-platform design system used by 15 engineers and 4 product teams.",
                "Delivered interactive prototypes in Figma, cutting design-to-development handoff time by 50%.",
                "Led visual rebrand initiative resulting in a 35% uplift in NPS and brand recognition scores.",
                "Collaborated directly with engineering daily in an agile sprint process, shipping features on schedule.",
                "Established accessibility guidelines (WCAG 2.1 AA), achieving compliance across the entire product.",
            ];
        }

        if ($this->matchRole($jt, ['product manager','product owner','program manager','project manager','scrum master'])) {
            return [
                "Owned product roadmap for a suite of 4 features, delivering \$2M ARR within 12 months of launch.",
                "Led cross-functional squads of 12 (engineering, design, data) in agile sprints, achieving 95% on-time delivery.",
                "Defined OKRs and KPIs aligned to company strategy, presenting quarterly results to C-suite.",
                "Reduced customer churn by 18% through a data-driven re-engagement programme.",
                "Ran discovery sprints with 20+ customer interviews per quarter to prioritise the backlog.",
                "Managed stakeholder expectations across 6 departments, resolving conflicting priorities proactively.",
                "Introduced experiment-led culture, running 30+ A/B tests per quarter with a 40% win rate.",
            ];
        }

        if ($this->matchRole($jt, ['marketing','growth','seo','content','brand','digital marketing','copywriter'])) {
            return [
                "Drove 120% YoY organic traffic growth through a data-driven SEO and content strategy.",
                "Managed paid campaigns (Google Ads, Meta) with a \$500k budget, achieving 3.2× ROAS.",
                "Launched email nurture sequences that improved MQL-to-SQL conversion rate by 45%.",
                "Built influencer partnership programme generating 2M+ impressions per quarter.",
                "Wrote and edited 50+ long-form articles per month, ranking 15 keywords in top-3 positions.",
                "Collaborated with product and sales to develop go-to-market strategies for 3 product launches.",
                "Analysed campaign performance weekly, pivoting strategy to reduce CPA by 30%.",
            ];
        }

        if ($this->matchRole($jt, ['sales','account executive','business development','account manager'])) {
            return [
                "Exceeded annual quota by 135%, generating \$1.8M in new ARR through enterprise account expansion.",
                "Prospected and closed 40+ net-new accounts, maintaining a consistent 30% win rate.",
                "Managed a portfolio of 80 accounts, achieving 96% renewal rate through proactive relationship management.",
                "Collaborated with solution engineering to deliver tailored demos, reducing sales cycle by 25%.",
                "Built and maintained a healthy \$4M pipeline using Salesforce and outbound prospecting techniques.",
                "Partnered with marketing on ABM campaigns targeting Fortune 500 companies, generating 60 SQLs per quarter.",
                "Presented at 5 industry conferences, generating 200+ qualified leads per event.",
            ];
        }

        // Generic high-quality bullets
        return [
            "Spearheaded strategic initiatives{$co} that delivered measurable improvements in team efficiency and product quality.",
            "Collaborated cross-functionally with stakeholders to translate business requirements into executable action plans.",
            "Identified and resolved systemic bottlenecks, resulting in a 30% improvement in operational throughput.",
            "Mentored peers and junior colleagues, fostering a high-performance culture and knowledge-sharing practices.",
            "Developed and presented data-driven insights to leadership, directly informing quarterly strategic decisions.",
            "Streamlined workflows through process documentation and automation, saving 8+ hours per week.",
            "Built trusted relationships with internal and external partners, ensuring projects delivered on scope and schedule.",
        ];
    }

    private function matchRole(string $jt, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($jt, $kw)) return true;
        }
        return false;
    }

    private function getSkillsForRole(string $jobTitle): array
    {
        $jt = strtolower($jobTitle);

        if ($this->matchRole($jt, ['php','backend','laravel','symfony','codeigniter'])) {
            return ['PHP', 'Laravel', 'MySQL', 'REST API', 'Git', 'Docker', 'Linux', 'JavaScript', 'HTML/CSS', 'Redis', 'PostgreSQL', 'Nginx', 'PHPUnit', 'Composer', 'AWS'];
        }
        if ($this->matchRole($jt, ['frontend','react','vue','angular','next.js','nuxt'])) {
            return ['React', 'Vue.js', 'TypeScript', 'JavaScript', 'HTML5', 'CSS3', 'Tailwind CSS', 'Git', 'Webpack', 'Figma', 'Next.js', 'REST API', 'Jest', 'Redux', 'Vite'];
        }
        if ($this->matchRole($jt, ['fullstack','full stack','full-stack','web developer','node'])) {
            return ['JavaScript', 'TypeScript', 'Node.js', 'React', 'PHP', 'MySQL', 'MongoDB', 'Docker', 'AWS', 'Git', 'REST API', 'GraphQL', 'Redis', 'Linux', 'CI/CD'];
        }
        if ($this->matchRole($jt, ['python','django','flask','fastapi'])) {
            return ['Python', 'Django', 'FastAPI', 'Flask', 'PostgreSQL', 'Redis', 'Docker', 'AWS', 'Git', 'REST API', 'Celery', 'SQLAlchemy', 'Linux', 'CI/CD', 'Pytest'];
        }
        if ($this->matchRole($jt, ['data scientist','machine learning','ml engineer','ai engineer','nlp','deep learning'])) {
            return ['Python', 'TensorFlow', 'PyTorch', 'Pandas', 'NumPy', 'SQL', 'Scikit-learn', 'Jupyter', 'Tableau', 'Spark', 'Keras', 'MLflow', 'Git', 'Docker', 'Statistics'];
        }
        if ($this->matchRole($jt, ['data analyst','business analyst','bi analyst','power bi','tableau'])) {
            return ['Python', 'SQL', 'Power BI', 'Tableau', 'Excel', 'R', 'Google Analytics', 'Looker', 'Data Modelling', 'ETL', 'Storytelling', 'Statistics', 'Git', 'Airflow', 'BigQuery'];
        }
        if ($this->matchRole($jt, ['data engineer','etl','pipeline','spark','airflow'])) {
            return ['Python', 'SQL', 'Apache Spark', 'Airflow', 'dbt', 'Kafka', 'Snowflake', 'BigQuery', 'Docker', 'AWS', 'Git', 'Linux', 'Terraform', 'Databricks', 'CI/CD'];
        }
        if ($this->matchRole($jt, ['devops','sre','cloud','infrastructure','platform','k8s','kubernetes','terraform','ansible'])) {
            return ['Docker', 'Kubernetes', 'AWS', 'Terraform', 'CI/CD', 'Linux', 'Ansible', 'Prometheus', 'Git', 'Python', 'Helm', 'ArgoCD', 'GitHub Actions', 'Bash', 'Grafana'];
        }
        if ($this->matchRole($jt, ['designer','ux','ui','product designer','visual','figma','sketch'])) {
            return ['Figma', 'Adobe XD', 'Sketch', 'Prototyping', 'User Research', 'Wireframing', 'CSS', 'Design Systems', 'Accessibility', 'Usability Testing', 'After Effects', 'Illustrator', 'Photoshop', 'Principle', 'Lottie'];
        }
        if ($this->matchRole($jt, ['project manager','program manager','scrum','agile','product owner','delivery'])) {
            return ['Agile', 'Scrum', 'Jira', 'Risk Management', 'Stakeholder Communication', 'Budgeting', 'Confluence', 'MS Project', 'Kanban', 'PMP', 'Roadmapping', 'OKRs', 'Miro', 'Notion', 'Trello'];
        }
        if ($this->matchRole($jt, ['product manager','product lead','growth product'])) {
            return ['Product Strategy', 'Roadmapping', 'OKRs', 'A/B Testing', 'User Research', 'Analytics', 'Jira', 'Figma', 'SQL', 'Stakeholder Management', 'Prioritisation', 'Go-to-Market', 'Agile', 'Amplitude', 'Mixpanel'];
        }
        if ($this->matchRole($jt, ['marketing','seo','content','brand','digital','copywriter','growth'])) {
            return ['SEO/SEM', 'Google Analytics', 'Content Strategy', 'Social Media', 'Email Marketing', 'HubSpot', 'Copywriting', 'A/B Testing', 'PPC', 'CRM', 'Mailchimp', 'Canva', 'Google Ads', 'Meta Ads', 'Ahrefs'];
        }
        if ($this->matchRole($jt, ['android','kotlin','jetpack'])) {
            return ['Kotlin', 'Java', 'Android SDK', 'Jetpack Compose', 'REST API', 'Firebase', 'Git', 'MVVM', 'Coroutines', 'Unit Testing', 'Room DB', 'Hilt', 'Retrofit', 'CI/CD', 'Play Store'];
        }
        if ($this->matchRole($jt, ['ios','swift','swiftui','xcode','apple'])) {
            return ['Swift', 'SwiftUI', 'Xcode', 'UIKit', 'Core Data', 'REST API', 'Git', 'MVVM', 'Combine', 'TestFlight', 'AVFoundation', 'CloudKit', 'Push Notifications', 'App Store', 'CI/CD'];
        }
        if ($this->matchRole($jt, ['sales','account executive','business development','account manager','bdr','sdr'])) {
            return ['Salesforce', 'HubSpot', 'Outbound Prospecting', 'CRM', 'Negotiation', 'Pipeline Management', 'Cold Outreach', 'B2B Sales', 'SaaS', 'LinkedIn Sales Navigator', 'Account Management', 'Demo Skills', 'Contract Negotiation', 'ABM', 'Forecasting'];
        }
        if ($this->matchRole($jt, ['hr','human resources','talent','recruiter','people','hris'])) {
            return ['HRIS', 'Recruitment', 'Onboarding', 'Performance Management', 'Employment Law', 'Workday', 'BambooHR', 'Talent Acquisition', 'Employee Relations', 'Compensation & Benefits', 'ATS', 'LinkedIn Recruiter', 'L&D', 'OKRs', 'Culture Building'];
        }
        if ($this->matchRole($jt, ['finance','accountant','analyst','cpa','cfa','auditor','controller'])) {
            return ['Financial Modelling', 'Excel', 'SQL', 'SAP', 'QuickBooks', 'Forecasting', 'Budgeting', 'GAAP', 'IFRS', 'Variance Analysis', 'Power BI', 'ERP', 'Tax Compliance', 'Audit', 'Bloomberg'];
        }

        // Generic
        return ['Communication', 'Problem Solving', 'Team Collaboration', 'Critical Thinking', 'Microsoft Office', 'Project Management', 'Time Management', 'Leadership', 'Adaptability', 'Data Analysis', 'Presentation Skills', 'Conflict Resolution', 'Strategic Planning', 'Stakeholder Management', 'Attention to Detail'];
    }
}
