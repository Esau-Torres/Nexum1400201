<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReciboPagoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $numeroRecibo;
    public $total;
    public $detalles; // Aquí enviaremos la lista de TODO lo que pagó
    public $metodoPago;
    public $nombreCajero;
    public $fecha;
    public $nombreEstudiante;
    public $codigoEstudiante;

    public function __construct($numeroRecibo, $total, $detalles, $metodoPago, $nombreCajero, $fecha, $nombreEstudiante, $codigoEstudiante = 'N/A')
    {
        $this->numeroRecibo = $numeroRecibo;
        $this->total = $total;
        $this->detalles = $detalles;
        $this->metodoPago = $metodoPago;
        $this->nombreCajero = $nombreCajero;
        $this->fecha = $fecha;
        $this->nombreEstudiante = $nombreEstudiante;
        $this->codigoEstudiante = $codigoEstudiante;
    }

    public function build()
    {
        return $this->subject('Comprobante de Pago - Universidad Modular Abierta')
                    ->view('mails.cajero.recibopago'); // Apunta a la vista que crearemos
    }
}
