<?php

namespace App\Services;

use App\Models\Comprobante;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    /**
     * Genera el binario PDF del comprobante de entrada.
     */
    public function generarComprobantePdf(Comprobante $comprobante): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);

        $html = view('pdf.comprobante', compact('comprobante'))->render();

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }
}
