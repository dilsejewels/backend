<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;
    public $responseMessage;
    public $responderName;

    /**
     * Create a new message instance.
     */
    public function __construct($contact, $responseMessage, $responderName = null)
    {
        $this->contact = $contact;
        $this->responseMessage = $responseMessage;
        $this->responderName = $responderName ?? 'Admin';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Response to your contact query - ' . config('app.name'))
            ->view('admin.emails.contact_response')
            ->with([
                'contact' => $this->contact,
                'responseMessage' => $this->responseMessage,
                'responderName' => $this->responderName,
            ]);
    }
}
