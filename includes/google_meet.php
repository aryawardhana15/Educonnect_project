<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

class GoogleMeet {
    private $client;
    private $service;
    
    public function __construct() {
        $this->client = new Google_Client();
        $this->client->setAuthConfig('credentials.json');
        $this->client->addScope(Google_Service_Calendar::CALENDAR);
        $this->client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
        
        $this->service = new Google_Service_Calendar($this->client);
    }
    
    public function createMeeting($mentor_email, $student_email, $topic, $start_time, $duration_minutes) {
        try {
            $event = new Google_Service_Calendar_Event(array(
                'summary' => "Mentoring Session: " . $topic,
                'description' => "Mentoring session between mentor and student",
                'start' => array(
                    'dateTime' => $start_time,
                    'timeZone' => 'Asia/Jakarta',
                ),
                'end' => array(
                    'dateTime' => date('c', strtotime($start_time . ' + ' . $duration_minutes . ' minutes')),
                    'timeZone' => 'Asia/Jakarta',
                ),
                'attendees' => array(
                    array('email' => $mentor_email),
                    array('email' => $student_email),
                ),
                'conferenceData' => array(
                    'createRequest' => array(
                        'requestId' => uniqid(),
                        'conferenceSolutionKey' => array(
                            'type' => 'hangoutsMeet'
                        )
                    )
                )
            ));
            
            $event = $this->service->events->insert('primary', $event, array(
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all'
            ));
            
            return array(
                'success' => true,
                'meeting_link' => $event->getHangoutLink(),
                'event_id' => $event->getId()
            );
        } catch (Exception $e) {
            error_log("Google Meet creation failed: " . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    public function cancelMeeting($event_id) {
        try {
            $this->service->events->delete('primary', $event_id, array(
                'sendUpdates' => 'all'
            ));
            return true;
        } catch (Exception $e) {
            error_log("Google Meet cancellation failed: " . $e->getMessage());
            return false;
        }
    }
} 