<?php

declare(strict_types=1);

/**
 * Handle job seeker application POST from dashboard or jobs page.
 * Redirects to $redirectPath when action=apply is processed.
 */
function job_apply_handle_post(PDO $pdo, int $jobSeekerUserId, string $redirectPath): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }
    if (($_POST['action'] ?? '') !== 'apply') {
        return;
    }
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
        redirect($redirectPath);
    }

    $jobId = (int) ($_POST['job_id'] ?? 0);
    $cover = trim((string) ($_POST['cover_letter'] ?? ''));
    $chk = $pdo->prepare("
        SELECT j.id, j.recruiter_id, j.title, u.email as recruiter_email 
        FROM jobs j 
        JOIN users u ON u.id = j.recruiter_id 
        WHERE j.id = ? AND j.status = 'published' 
        LIMIT 1
    ");
    $chk->execute([$jobId]);
    $job = $chk->fetch();
    if (!$job) {
        flash_set('warning', 'That job is not available.');
        redirect($redirectPath);
    }

    try {
        $ins = $pdo->prepare(
            'INSERT INTO applications (job_id, job_seeker_id, status, cover_letter) VALUES (?,?,?,?)'
        );
        $ins->execute([$jobId, $jobSeekerUserId, 'submitted', $cover]);
        
        // Internal Notification
        notify_user($pdo, (int) $job['recruiter_id'], 'New application for: ' . $job['title'], 'info');
        notify_user($pdo, $jobSeekerUserId, 'Application submitted for ' . $job['title'] . '.', 'success');
        
        $cu = current_user();
        $seekerEmail = (string) ($cu['email'] ?? '');
        $seekerName = (string) ($cu['name'] ?? 'A candidate');

        // Notify Seeker
        if ($seekerEmail !== '') {
            send_notification_email(
                $seekerEmail,
                'Application received',
                "Success! You've applied to '" . $job['title'] . "'. We'll let you know when the recruiter updates your status."
            );
        }

        // Notify Recruiter
        $recruiterEmail = (string) $job['recruiter_email'];
        if ($recruiterEmail !== '') {
            $msg = "Hello,\n\n" .
                   "You have a new applicant for your listing: '" . $job['title'] . "'.\n" .
                   "Candidate: " . $seekerName . " (" . $seekerEmail . ")\n\n" .
                   "View your dashboard to review their profile and cover letter:\n" .
                   app_url('recruiter/applicants.php', true);

            send_notification_email(
                $recruiterEmail,
                'New Applicant: ' . $job['title'],
                $msg
            );
        }
        flash_set('success', 'Application submitted. You can track it under My applications.');
    } catch (PDOException $e) {
        if ((int) $e->getCode() === 23000) {
            flash_set('warning', 'You have already applied to this job.');
        } else {
            flash_set('danger', 'Could not submit application.');
        }
    }

    redirect($redirectPath);
}
