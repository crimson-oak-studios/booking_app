<?php
namespace App\Services;
use App\Models\Appointment;
class SquarePaymentService {
    public function createPaymentLink(Appointment $appointment): array {
        return ['id' => 'sq_link_'.$appointment->id, 'url' => url('/payments/placeholder/'.$appointment->id)];
    }
}
