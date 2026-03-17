<?php
/**
 * ResumeX AI Controller
 * Provides intelligent rule-based suggestions.
 *
 * @package MMB\Projects\ResumeX\Controllers
 */

namespace Projects\ResumeX\Controllers;

use Core\Security;

class AIController
{
    public function __construct()
    {
        header('Content-Type: application/json');
    }

    /* ── suggest-summary ──────────────────────────────────────── */
    public function suggestSummary(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle   = trim($_POST['job_title']        ?? '');
        $experience = (int) ($_POST['experience_years'] ?? 0);
        $skills     = trim($_POST['skills']            ?? '');

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        echo json_encode(['success' => true, 'suggestions' => $this->buildSummaries($jobTitle, $experience, $skills)]);
        exit;
    }

    /* ── suggest-skills ───────────────────────────────────────── */
    public function suggestSkills(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle = trim($_POST['job_title'] ?? '');

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        echo json_encode(['success' => true, 'skills' => $this->getSkillsForRole($jobTitle)]);
        exit;
    }

    /* ── suggest-bullets ──────────────────────────────────────── */
    public function suggestBullets(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle = trim($_POST['job_title'] ?? '');
        $company  = trim($_POST['company']   ?? '');

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        echo json_encode(['success' => true, 'bullets' => $this->buildBullets($jobTitle, $company)]);
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
        $yrs = $years > 0 ? "{$years}+" : 'several';
        $sk  = !empty($skills) ? ", with expertise in {$skills}," : '';
        $yr  = $years > 0 ? " Over {$years} years" : ' Extensive';

        return [
            "Results-driven {$jt} with {$yrs} years of proven experience{$sk} delivering high-impact solutions. Passionate about clean architecture and measurable business outcomes, with a track record of leading teams and exceeding targets.",
            "Innovative {$jt}{$sk} combining {$yrs} years of hands-on expertise with a strategic mindset.{$yr} of experience collaborating cross-functionally to ship products on time and within budget.",
            "Dynamic {$jt} with {$yrs} years of progressive experience{$sk}. Adept at turning complex challenges into elegant solutions, with a consistent focus on quality, performance, and continuous improvement.",
            "Accomplished {$jt} bringing {$yrs} years of industry experience{$sk}. Known for building collaborative team environments, mentoring talent, and driving technical roadmaps aligned with business priorities.",
            "Detail-oriented {$jt} with {$yrs} years of experience{$sk}. Skilled at bridging the gap between technical teams and stakeholders, communicating complex ideas clearly and delivering reliable, scalable solutions.",
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
