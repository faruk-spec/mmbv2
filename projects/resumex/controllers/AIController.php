<?php
/**
 * ResumeX AI Controller
 * Provides intelligent suggestions powered by server-side rule-based engine.
 *
 * @package MMB\Projects\ResumeX\Controllers
 */

namespace Projects\ResumeX\Controllers;

use Core\Auth;
use Core\Security;

class AIController
{
    public function __construct()
    {
        header('Content-Type: application/json');
    }

    /**
     * Suggest a professional summary based on job title + years of experience
     */
    public function suggestSummary(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle    = trim($_POST['job_title'] ?? '');
        $experience  = (int) ($_POST['experience_years'] ?? 0);
        $skills      = trim($_POST['skills'] ?? '');

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        $summaries = $this->buildSummaries($jobTitle, $experience, $skills);

        echo json_encode(['success' => true, 'suggestions' => $summaries]);
        exit;
    }

    /**
     * Suggest skills based on job title
     */
    public function suggestSkills(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle = trim($_POST['job_title'] ?? '');

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        $skills = $this->getSkillsForRole($jobTitle);

        echo json_encode(['success' => true, 'skills' => $skills]);
        exit;
    }

    /**
     * Suggest bullet points for a work experience entry
     */
    public function suggestBullets(): void
    {
        Security::validateCsrfToken($this->getToken());

        $jobTitle = trim($_POST['job_title'] ?? '');
        $company  = trim($_POST['company']   ?? '');

        if (empty($jobTitle)) {
            echo json_encode(['success' => false, 'message' => 'Job title is required']);
            exit;
        }

        $bullets = $this->buildBullets($jobTitle, $company);

        echo json_encode(['success' => true, 'bullets' => $bullets]);
        exit;
    }

    /**
     * Score the resume and return improvement suggestions
     */
    public function score(): void
    {
        Security::validateCsrfToken($this->getToken());

        $body       = file_get_contents('php://input');
        $payload    = json_decode($body, true) ?? [];
        $resumeData = $payload['resume_data'] ?? [];

        $score       = 0;
        $suggestions = [];
        $breakdown   = [];

        // Contact info check (20 pts)
        $contact       = $resumeData['contact'] ?? [];
        $contactScore  = 0;
        $contactFields = ['name', 'email', 'phone', 'location'];
        foreach ($contactFields as $f) {
            if (!empty($contact[$f])) {
                $contactScore += 5;
            } else {
                $suggestions[] = "Add your {$f} to contact information.";
            }
        }
        $breakdown['contact'] = $contactScore;
        $score += $contactScore;

        // Summary check (20 pts)
        $summary = $resumeData['summary'] ?? '';
        $summaryLen = strlen($summary);
        if ($summaryLen >= 200) {
            $breakdown['summary'] = 20;
            $score += 20;
        } elseif ($summaryLen >= 100) {
            $breakdown['summary'] = 10;
            $score += 10;
            $suggestions[] = 'Expand your professional summary (aim for 200+ characters).';
        } else {
            $breakdown['summary'] = 0;
            $suggestions[] = 'Add a professional summary to stand out.';
        }

        // Experience check (25 pts)
        $experience      = $resumeData['experience'] ?? [];
        $experienceScore = 0;
        if (count($experience) >= 2) {
            $experienceScore = 25;
        } elseif (count($experience) === 1) {
            $experienceScore = 15;
            $suggestions[] = 'Add more work experience entries.';
        } else {
            $suggestions[] = 'Add at least one work experience entry.';
        }
        $breakdown['experience'] = $experienceScore;
        $score += $experienceScore;

        // Skills check (15 pts)
        $skills      = $resumeData['skills'] ?? [];
        $skillsScore = 0;
        if (count($skills) >= 8) {
            $skillsScore = 15;
        } elseif (count($skills) >= 4) {
            $skillsScore = 8;
            $suggestions[] = 'Add more skills (aim for at least 8).';
        } else {
            $suggestions[] = 'Add relevant skills to your resume.';
        }
        $breakdown['skills'] = $skillsScore;
        $score += $skillsScore;

        // Education check (10 pts)
        $education      = $resumeData['education'] ?? [];
        $educationScore = !empty($education) ? 10 : 0;
        if (empty($education)) {
            $suggestions[] = 'Add your education background.';
        }
        $breakdown['education'] = $educationScore;
        $score += $educationScore;

        // Extra sections (10 pts)
        $extraScore = 0;
        $extraSections = ['projects', 'certifications', 'awards', 'publications'];
        foreach ($extraSections as $section) {
            if (!empty($resumeData[$section])) {
                $extraScore += 2.5;
            }
        }
        $breakdown['extras'] = (int) $extraScore;
        $score += (int) $extraScore;

        $score = min(100, (int) $score);

        $grade = $score >= 90 ? 'A' : ($score >= 75 ? 'B' : ($score >= 60 ? 'C' : ($score >= 45 ? 'D' : 'F')));

        echo json_encode([
            'success'     => true,
            'score'       => $score,
            'grade'       => $grade,
            'breakdown'   => $breakdown,
            'suggestions' => $suggestions,
        ]);
        exit;
    }

    // -----------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------

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
        $jt   = ucwords($jobTitle);
        $yrs  = $years > 0 ? "{$years}+ years of" : 'extensive';
        $sk   = !empty($skills) ? " with expertise in {$skills}" : '';

        return [
            "Results-driven {$jt} with {$yrs} experience{$sk}. Passionate about delivering high-quality solutions and driving measurable business impact.",
            "Innovative {$jt} combining {$yrs} hands-on experience{$sk}. Proven track record of leading cross-functional teams and exceeding performance targets.",
            "Dynamic {$jt} with {$yrs} progressive experience{$sk}. Adept at problem-solving, collaboration, and continuous improvement in fast-paced environments.",
        ];
    }

    private function buildBullets(string $jobTitle, string $company): array
    {
        $jt = strtolower($jobTitle);
        $co = $company ? " at {$company}" : '';

        $bullets = [
            "Led end-to-end development of key features{$co}, reducing delivery time by 30%.",
            "Collaborated with cross-functional teams to define requirements and deliver scalable solutions.",
            "Identified and resolved critical production issues, improving system reliability by 25%.",
            "Mentored junior team members and conducted code reviews to uphold quality standards.",
            "Implemented automated testing pipelines, reducing regression bugs by 40%.",
            "Designed and optimised database schemas resulting in 50% faster query performance.",
            "Presented technical roadmaps to stakeholders, aligning engineering priorities with business goals.",
        ];

        // Add role-specific bullets
        if (str_contains($jt, 'developer') || str_contains($jt, 'engineer')) {
            $bullets[] = "Architected microservices infrastructure supporting 100k+ daily active users.";
            $bullets[] = "Refactored legacy codebase reducing technical debt by 35% and improving maintainability.";
        } elseif (str_contains($jt, 'designer') || str_contains($jt, 'ux')) {
            $bullets[] = "Conducted user research and usability testing to inform design decisions.";
            $bullets[] = "Created interactive prototypes that reduced design-to-development handoff time by 50%.";
        } elseif (str_contains($jt, 'manager') || str_contains($jt, 'lead')) {
            $bullets[] = "Managed a team of 8 professionals, fostering a culture of innovation and accountability.";
            $bullets[] = "Developed and tracked KPIs resulting in 20% improvement in team productivity.";
        } elseif (str_contains($jt, 'data') || str_contains($jt, 'analyst')) {
            $bullets[] = "Built dashboards and reports using Power BI / Tableau to support data-driven decisions.";
            $bullets[] = "Developed predictive models achieving 92% accuracy, enabling proactive business strategies.";
        }

        return array_slice($bullets, 0, 5);
    }

    private function getSkillsForRole(string $jobTitle): array
    {
        $jt     = strtolower($jobTitle);
        $skills = [];

        if (str_contains($jt, 'php') || str_contains($jt, 'backend') || str_contains($jt, 'web developer')) {
            $skills = ['PHP', 'Laravel', 'MySQL', 'REST API', 'Git', 'Docker', 'Linux', 'JavaScript', 'HTML/CSS', 'Redis'];
        } elseif (str_contains($jt, 'frontend') || str_contains($jt, 'react') || str_contains($jt, 'vue')) {
            $skills = ['React', 'Vue.js', 'TypeScript', 'JavaScript', 'HTML5', 'CSS3', 'Tailwind CSS', 'Git', 'Webpack', 'Figma'];
        } elseif (str_contains($jt, 'fullstack') || str_contains($jt, 'full stack') || str_contains($jt, 'full-stack')) {
            $skills = ['JavaScript', 'Node.js', 'React', 'PHP', 'MySQL', 'MongoDB', 'Docker', 'AWS', 'Git', 'REST API'];
        } elseif (str_contains($jt, 'data scientist') || str_contains($jt, 'machine learning') || str_contains($jt, 'ai')) {
            $skills = ['Python', 'TensorFlow', 'PyTorch', 'Pandas', 'NumPy', 'SQL', 'R', 'Scikit-learn', 'Jupyter', 'Tableau'];
        } elseif (str_contains($jt, 'devops') || str_contains($jt, 'cloud') || str_contains($jt, 'sre')) {
            $skills = ['Docker', 'Kubernetes', 'AWS', 'Terraform', 'CI/CD', 'Linux', 'Ansible', 'Prometheus', 'Git', 'Python'];
        } elseif (str_contains($jt, 'designer') || str_contains($jt, 'ux') || str_contains($jt, 'ui')) {
            $skills = ['Figma', 'Adobe XD', 'Sketch', 'Prototyping', 'User Research', 'Wireframing', 'CSS', 'Design Systems', 'Accessibility', 'Usability Testing'];
        } elseif (str_contains($jt, 'project manager') || str_contains($jt, 'scrum') || str_contains($jt, 'agile')) {
            $skills = ['Agile', 'Scrum', 'Jira', 'Risk Management', 'Stakeholder Communication', 'Budgeting', 'Confluence', 'MS Project', 'Kanban', 'PMP'];
        } elseif (str_contains($jt, 'marketing') || str_contains($jt, 'seo') || str_contains($jt, 'content')) {
            $skills = ['SEO/SEM', 'Google Analytics', 'Content Strategy', 'Social Media', 'Email Marketing', 'HubSpot', 'Copywriting', 'A/B Testing', 'PPC', 'CRM'];
        } elseif (str_contains($jt, 'android') || str_contains($jt, 'kotlin') || str_contains($jt, 'mobile')) {
            $skills = ['Kotlin', 'Java', 'Android SDK', 'Jetpack Compose', 'REST API', 'Firebase', 'Git', 'MVVM', 'Coroutines', 'Unit Testing'];
        } elseif (str_contains($jt, 'ios') || str_contains($jt, 'swift') || str_contains($jt, 'apple')) {
            $skills = ['Swift', 'SwiftUI', 'Xcode', 'UIKit', 'Core Data', 'REST API', 'Git', 'MVVM', 'Combine', 'TestFlight'];
        } else {
            // Generic professional skills
            $skills = ['Communication', 'Problem Solving', 'Team Collaboration', 'Critical Thinking', 'Microsoft Office', 'Project Management', 'Time Management', 'Leadership', 'Adaptability', 'Data Analysis'];
        }

        return $skills;
    }
}
