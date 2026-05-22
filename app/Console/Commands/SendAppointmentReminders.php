<?php
namespace App\Console\Commands;
use App\Mail\BookingConfirmationMail;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
class SendAppointmentReminders extends Command {
 protected $signature='appointments:send-reminders';
 protected $description='Send 24-hour appointment reminder emails';
 public function handle(): int {
  $from=now()->addHours(24)->startOfHour(); $to=now()->addHours(24)->endOfHour();
  Appointment::where('status','booked')->whereBetween('starts_at',[$from,$to])->get()->each(fn($a)=>Mail::to($a->customer->email)->send(new BookingConfirmationMail($a)));
  $this->info('Reminders sent'); return self::SUCCESS;
 }
}
