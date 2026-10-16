<?php

namespace App\Mail;

use App\Models\Comprobante;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComprobanteCompraMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Comprobante $comprobante
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎟️ Tus Entradas y Comprobante Cine+ [#'.$this->comprobante->codigo_reserva.']',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.comprobante',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdfService = app(PdfService::class);
        $pdfContent = $pdfService->generarComprobantePdf($this->comprobante);

        return [
            Attachment::fromData(fn () => $pdfContent, 'CinePlus_Comprobante_'.$this->comprobante->codigo_reserva.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
