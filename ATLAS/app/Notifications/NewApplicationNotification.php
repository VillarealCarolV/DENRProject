<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewApplicationNotification extends Notification
{
    use Queueable;

    public $application;

    // Pass the new application data into the notification
    public function __construct($application)
    {
        // Ensure relationships are loaded
        $this->application = $application->load(['applicant', 'landRecord', 'statusHistories']);
    }

    // Tell Laravel to save this in the database
    public function via($notifiable)
    {
        return ['database'];
    }

    // What data should be saved in the database for the bell icon?
    public function toArray($notifiable)
    {
        $applicant = $this->application->applicant;
        $landRecord = $this->application->landRecord;
        
        // Get the latest status from status_histories
        $latestStatusHistory = $this->application->statusHistories()->latest()->first();
        $status = $latestStatusHistory?->status ?? 'pending';

        return [
            // For Dropdown List
            'tracking_no' => $this->application->tracking_no,
            'message' => 'A new application is waiting for processing.',
            
            // For Modal Details
            'applicant_name' => $applicant?->full_name ?? 'N/A',
            'location' => $landRecord?->location ?? 'N/A',
            'survey_no' => $landRecord?->survey_no ?? 'N/A',
            'status' => $status,
            'remarks' => $this->application->land_officer_remarks ?? '',
            'application_id' => $this->application->id,
            
            // For Navigation
            'url' => route('applications.show', $this->application->id)
        ];
    }
}